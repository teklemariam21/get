<?php
/**
 * Hitechcomputer Tutorial Scraper
 * Scrapes tutorials from external sites and imports them into the database.
 * Run from browser: http://localhost/get/scraper/scrape.php
 *
 * NOTE: Content is attributed to original sources. For educational/reference use.
 * Delete this file after importing.
 */

require_once __DIR__ . '/../config/db.php';

// ── Auth guard ──
if (!isset($_SESSION['admin_id'])) {
    die('Access denied. <a href="' . SITE_URL . '/admin/login.php">Login first</a>.');
}

set_time_limit(300);
ini_set('max_execution_time', 300);

// ── Sources ──
$SOURCES = [
    'blogger' => [
        'name'      => 'Cell Phone Repair Tutorials (Blogger)',
        'base_url'  => 'https://cellphonerepairtutorials.blogspot.com',
        'start_url' => 'https://cellphonerepairtutorials.blogspot.com/?m=1',
        'type'      => 'blogger',
        'category'  => 'tutorial',
        'max_pages' => 10,
    ],
    'mcr_tutorial' => [
        'name'      => 'Mobile Cell Phone Repairing – Tutorials',
        'base_url'  => 'https://www.mobilecellphonerepairing.com',
        'start_url' => 'https://www.mobilecellphonerepairing.com/category/mobile-phone-repairing-tutorial',
        'type'      => 'wordpress',
        'category'  => 'tutorial',
        'max_pages' => 10,
    ],
    'mcr_solution' => [
        'name'      => 'Mobile Cell Phone Repairing – Problem Solution',
        'base_url'  => 'https://www.mobilecellphonerepairing.com',
        'start_url' => 'https://www.mobilecellphonerepairing.com/mobile-phone-problem-solution',
        'type'      => 'wordpress',
        'category'  => 'trick',
        'max_pages' => 10,
    ],
];

// ── cURL fetch helper ──
function fetchURL(string $url): string {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING       => 'gzip, deflate',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
        ],
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 400) ? (string)$html : '';
}

// ── DOM helper ──
function getDom(string $html): DOMDocument {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();
    return $doc;
}

function xquery(DOMDocument|DOMElement $ctx, string $xpath): DOMNodeList {
    $xp = new DOMXPath($ctx instanceof DOMDocument ? $ctx : $ctx->ownerDocument);
    return $xp->query($xpath, $ctx instanceof DOMDocument ? null : $ctx) ?: new DOMNodeList();
}

function nodeText(DOMDocument $doc, string $xpath): string {
    $nl = xquery($doc, $xpath);
    return $nl->length ? trim($nl->item(0)->textContent) : '';
}

function nodeAttr(DOMDocument $doc, string $xpath, string $attr): string {
    $nl = xquery($doc, $xpath);
    return $nl->length ? trim($nl->item(0)->getAttribute($attr)) : '';
}

// ── Download image to uploads ──
function downloadImage(string $url): ?string {
    if (!$url || strpos($url, 'http') !== 0) return null;
    // Skip tiny placeholder images
    if (preg_match('/\b(1x1|pixel|blank|spacer)\b/i', $url)) return null;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible)',
    ]);
    $data = curl_exec($ch);
    $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$data || !preg_match('#image/(jpeg|png|webp|gif)#i', $mime, $m)) return null;

    $ext = strtolower($m[1]);
    $name = 'scrape_' . md5($url) . '.' . $ext;
    $path = UPLOAD_DIR . $name;
    if (!file_exists($path)) file_put_contents($path, $data);
    return $name;
}

// ── Clean HTML content ──
function cleanContent(string $html, string $baseUrl): string {
    // Fix relative URLs
    $html = preg_replace_callback('/(?:href|src)=["\']([^"\']*)["\']/', function($m) use ($baseUrl) {
        $url = $m[1];
        if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0 && $url !== '#') {
            $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
        }
        return str_replace($m[1], $url, $m[0]);
    }, $html);

    // Remove scripts, ads, nav elements
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
    $html = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $html);
    $html = preg_replace('/<(nav|header|footer|aside)\b[^>]*>.*?<\/\1>/si', '', $html);
    $html = preg_replace('/<!--.*?-->/s', '', $html);

    // Trim whitespace
    $html = preg_replace('/\s{3,}/', "\n\n", $html);

    return trim($html);
}

// ── Scrape a Blogger site ──
function scrapeBlogger(array $source, PDO $pdo, array &$log): int {
    $count = 0;
    $nextUrl = $source['start_url'];
    $pages = 0;

    while ($nextUrl && $pages < $source['max_pages']) {
        $pages++;
        $log[] = "📄 Fetching page $pages: $nextUrl";
        $html = fetchURL($nextUrl);
        if (!$html) { $log[] = "⚠️ Empty response, stopping."; break; }

        $doc = getDom($html);
        $xp  = new DOMXPath($doc);

        // Post links – Blogger uses various patterns
        $postLinks = [];
        foreach ([
            "//h3[contains(@class,'post-title')]//a",
            "//h2[contains(@class,'post-title')]//a",
            "//a[contains(@class,'post-title')]",
            "//div[contains(@class,'post-outer')]//h3/a",
            "//div[contains(@class,'post')]//h3/a",
            "//div[contains(@class,'post')]//h2/a",
        ] as $xpq) {
            $nodes = $xp->query($xpq);
            if ($nodes && $nodes->length > 0) {
                foreach ($nodes as $n) {
                    $href = $n->getAttribute('href');
                    if ($href && strpos($href, '#') === false) $postLinks[] = $href;
                }
                if (count($postLinks)) break;
            }
        }

        // Deduplicate
        $postLinks = array_unique($postLinks);
        $log[] = "   Found " . count($postLinks) . " post links.";

        foreach ($postLinks as $postUrl) {
            $result = scrapeAndSavePost($postUrl, $source, $pdo, $log, 'blogger');
            if ($result) $count++;
            usleep(600000); // 0.6s delay
        }

        // Next page link
        $nextUrl = '';
        foreach ([
            "//a[contains(@id,'Blog1_blog-pager-older-link')]",
            "//a[contains(@class,'blog-pager-older-link')]",
            "//a[@id='Blog1_blog-pager-older-link']",
        ] as $xpq) {
            $nl = $xp->query($xpq);
            if ($nl && $nl->length > 0) {
                $href = $nl->item(0)->getAttribute('href');
                if ($href) { $nextUrl = $href; break; }
            }
        }

        if ($nextUrl) usleep(1500000); // 1.5s between pages
    }

    return $count;
}

// ── Scrape a WordPress site ──
function scrapeWordPress(array $source, PDO $pdo, array &$log): int {
    $count = 0;
    $nextUrl = $source['start_url'];
    $pages = 0;

    while ($nextUrl && $pages < $source['max_pages']) {
        $pages++;
        $log[] = "📄 Fetching page $pages: $nextUrl";
        $html = fetchURL($nextUrl);
        if (!$html) { $log[] = "⚠️ Empty response, stopping."; break; }

        $doc = getDom($html);
        $xp  = new DOMXPath($doc);

        // Article/post links
        $postLinks = [];
        foreach ([
            "//article//h2[contains(@class,'entry-title')]//a",
            "//article//h3[contains(@class,'entry-title')]//a",
            "//h2[contains(@class,'entry-title')]//a",
            "//h3[contains(@class,'entry-title')]//a",
            "//div[contains(@class,'entry-title')]//a",
            "//div[contains(@class,'post')]//h2/a",
            "//div[contains(@class,'post')]//h3/a",
            "//article//h2/a",
            "//article//h1/a",
        ] as $xpq) {
            $nodes = $xp->query($xpq);
            if ($nodes && $nodes->length > 0) {
                foreach ($nodes as $n) {
                    $href = $n->getAttribute('href');
                    if ($href && strpos($href, 'http') === 0) $postLinks[] = $href;
                }
                if (count($postLinks)) break;
            }
        }

        $postLinks = array_unique($postLinks);
        $log[] = "   Found " . count($postLinks) . " post links.";

        foreach ($postLinks as $postUrl) {
            $result = scrapeAndSavePost($postUrl, $source, $pdo, $log, 'wordpress');
            if ($result) $count++;
            usleep(800000); // 0.8s delay
        }

        // Pagination: next page
        $nextUrl = '';
        foreach ([
            "//a[contains(@class,'next') and contains(@class,'page-numbers')]",
            "//a[@class='next page-numbers']",
            "//div[contains(@class,'pagination')]//a[contains(text(),'Next')]",
            "//a[contains(@rel,'next')]",
            "//nav[contains(@class,'navigation')]//a[contains(@class,'next')]",
        ] as $xpq) {
            $nl = $xp->query($xpq);
            if ($nl && $nl->length > 0) {
                $href = $nl->item(0)->getAttribute('href');
                if ($href) { $nextUrl = $href; break; }
            }
        }

        if ($nextUrl) usleep(2000000); // 2s between pages
    }

    return $count;
}

// ── Scrape individual post & save ──
function scrapeAndSavePost(string $url, array $source, PDO $pdo, array &$log, string $siteType): bool {
    // Check if already exists (by URL stored in tags or slug)
    $urlHash = md5($url);
    $exists = $pdo->prepare("SELECT id FROM posts WHERE tags LIKE ?");
    $exists->execute(["%src_$urlHash%"]);
    if ($exists->fetch()) {
        $log[] = "   ⏭️ Already imported: $url";
        return false;
    }

    $html = fetchURL($url);
    if (!$html) { $log[] = "   ❌ Failed to fetch: $url"; return false; }

    $doc = getDom($html);
    $xp  = new DOMXPath($doc);

    // ── Extract title ──
    $title = '';
    foreach ([
        "//h1[contains(@class,'entry-title')]",
        "//h1[contains(@class,'post-title')]",
        "//h1[@class='title']",
        "//meta[@property='og:title']/@content",
        "//title",
        "//h1",
    ] as $xpq) {
        $nl = $xp->query($xpq);
        if ($nl && $nl->length > 0) {
            $node = $nl->item(0);
            $title = $node->nodeType === XML_ATTRIBUTE_NODE ? $node->value : trim($node->textContent);
            if ($title) break;
        }
    }
    $title = trim(preg_replace('/\s+/', ' ', $title));
    if (!$title || strlen($title) < 5) { $log[] = "   ⚠️ No title found: $url"; return false; }

    // ── Extract content ──
    $content = '';
    foreach ([
        "//div[contains(@class,'post-body')]",
        "//div[contains(@class,'entry-content')]",
        "//div[@class='post-content']",
        "//div[contains(@class,'article-content')]",
        "//div[@id='postContent']",
        "//article[contains(@class,'post')]",
        "//div[contains(@class,'td-post-content')]",
        "//div[@class='entry']",
    ] as $xpq) {
        $nl = $xp->query($xpq);
        if ($nl && $nl->length > 0) {
            $content = $doc->saveHTML($nl->item(0));
            if (strlen(strip_tags($content)) > 100) break;
        }
    }
    if (!$content || strlen(strip_tags($content)) < 80) {
        $log[] = "   ⚠️ Content too short, skipping: $url";
        return false;
    }
    $content = cleanContent($content, $source['base_url']);

    // ── Extract date ──
    $pubDate = null;
    foreach ([
        "//meta[@property='article:published_time']/@content",
        "//time[@class='published']/@datetime",
        "//abbr[@class='published']/@title",
        "//span[contains(@class,'date')]/@title",
        "//time/@datetime",
    ] as $xpq) {
        $nl = $xp->query($xpq);
        if ($nl && $nl->length > 0) {
            $val = $nl->item(0)->nodeValue ?? $nl->item(0)->value ?? '';
            if ($val) { $pubDate = date('Y-m-d H:i:s', strtotime($val)); break; }
        }
    }

    // ── Extract featured image ──
    $imageName = null;
    $imgUrl = '';
    foreach ([
        "//meta[@property='og:image']/@content",
        "//div[contains(@class,'post-body')]//img[1]/@src",
        "//div[contains(@class,'entry-content')]//img[1]/@src",
        "//article//img[1]/@src",
    ] as $xpq) {
        $nl = $xp->query($xpq);
        if ($nl && $nl->length > 0) {
            $v = $nl->item(0)->nodeValue ?? $nl->item(0)->value ?? '';
            if ($v && strpos($v, 'http') === 0 && !preg_match('/\b(avatar|logo|icon|badge)\b/i', $v)) {
                $imgUrl = $v; break;
            }
        }
    }
    if ($imgUrl) {
        $imageName = downloadImage($imgUrl);
        $log[] = "   🖼️ Image: " . ($imageName ? $imageName : 'download failed');
    }

    // ── Extract excerpt ──
    $excerpt = '';
    $plainText = strip_tags($content);
    $excerpt = substr(trim(preg_replace('/\s+/', ' ', $plainText)), 0, 200);

    // ── Build slug ──
    $slug = slugify($title);
    $baseSlug = $slug;
    $i = 1;
    while ($pdo->query("SELECT COUNT(*) FROM posts WHERE slug = " . $pdo->quote($slug))->fetchColumn() > 0) {
        $slug = $baseSlug . '-' . $i++;
    }

    // ── Tags: source URL hash for dedup + keywords ──
    $tags = 'mobile repair,phone repair,src_' . $urlHash;

    // ── Save to DB ──
    try {
        $stmt = $pdo->prepare("
            INSERT INTO posts (title, slug, excerpt, content, category, tags, image, status, featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'published', 0, ?, NOW())
        ");
        $stmt->execute([
            $title,
            $slug,
            $excerpt,
            '<p class="post-source" style="font-size:0.75rem;color:#888;margin-bottom:1rem">Source: <a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($url) . '</a></p>' . $content,
            $source['category'],
            $tags,
            $imageName,
            $pubDate ?? date('Y-m-d H:i:s'),
        ]);
        $log[] = "   ✅ Saved: <strong>" . htmlspecialchars($title) . "</strong>";
        return true;
    } catch (Exception $e) {
        $log[] = "   ❌ DB error: " . $e->getMessage();
        return false;
    }
}

// ============================================================
// ── RUN SCRAPER ──
// ============================================================
$runSource = $_GET['source'] ?? null;
$log       = [];
$imported  = 0;

if ($runSource && isset($SOURCES[$runSource])) {
    $src = $SOURCES[$runSource];
    $log[] = "<h4 style='color:var(--cyan)'>Scraping: {$src['name']}</h4>";
    if ($src['type'] === 'blogger') {
        $imported = scrapeBlogger($src, $pdo, $log);
    } else {
        $imported = scrapeWordPress($src, $pdo, $log);
    }
    $log[] = "<strong style='color:#00ff88'>Done! Imported $imported new posts.</strong>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial Scraper – Hitechcomputer Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .log-box { background:#000; border:1px solid var(--glass-border); border-radius:10px; padding:1.5rem; font-family:monospace; font-size:0.82rem; color:#aaffaa; max-height:500px; overflow-y:auto; line-height:1.7; }
        .log-box .warn { color:#ffcc00; }
        .log-box .err  { color:#ff6b6b; }
    </style>
</head>
<body class="admin-body" style="padding:0">
<?php include __DIR__ . '/../admin/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="admin-page-title"><i class="fas fa-robot me-2" style="color:var(--cyan)"></i>Tutorial Scraper</h1>
        <a href="<?= SITE_URL ?>/admin/posts.php" style="color:var(--text-muted);font-size:0.85rem;text-decoration:none">
            <i class="fas fa-arrow-left me-1"></i>Back to Posts
        </a>
    </div>

    <div class="admin-content">
        <!-- Warning -->
        <div class="alert-glass mb-4" style="border-color:rgba(255,193,7,0.4);background:rgba(255,193,7,0.05)">
            <i class="fas fa-exclamation-triangle" style="color:#ffc107"></i>
            <div>
                <strong>Attribution Notice:</strong> Scraped posts include a source link to the original article.
                This tool is for educational/reference use. Use content responsibly and give credit to original authors.
            </div>
        </div>

        <div class="row gy-4">
            <!-- Source Cards -->
            <div class="col-lg-5">
                <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">SELECT SOURCE TO SCRAPE</h6>

                <?php foreach ($SOURCES as $key => $src): ?>
                <div class="glass-card p-4 mb-3" style="<?= $runSource === $key ? 'border-color:rgba(0,212,255,0.4)' : '' ?>">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div style="font-weight:600;color:#fff;font-size:0.95rem;margin-bottom:4px"><?= htmlspecialchars($src['name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:8px;word-break:break-all"><?= htmlspecialchars($src['start_url']) ?></div>
                            <div class="d-flex gap-2">
                                <span class="post-category-badge badge-<?= $src['category'] ?>"><?= $src['category'] ?></span>
                                <span class="course-tag" style="font-size:0.7rem">Max <?= $src['max_pages'] ?> pages</span>
                            </div>
                        </div>
                        <a href="?source=<?= $key ?>" class="btn-glow flex-shrink-0"
                           style="font-size:0.78rem;padding:0.4rem 1rem;white-space:nowrap"
                           onclick="return confirmScrape(this)"
                        >
                            <i class="fas fa-download me-1"></i> Scrape
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Stats -->
                <?php
                $total = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
                $scraped = $pdo->query("SELECT COUNT(*) FROM posts WHERE tags LIKE '%src_%'")->fetchColumn();
                ?>
                <div class="glass-card p-3 mt-4">
                    <h6 style="font-family:'Orbitron',sans-serif;font-size:0.75rem;color:var(--text-muted);margin-bottom:0.8rem">DATABASE STATUS</h6>
                    <div class="d-flex justify-content-between mb-2" style="font-size:0.85rem">
                        <span style="color:var(--text-muted)">Total Posts</span>
                        <span style="color:#fff;font-weight:600"><?= $total ?></span>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size:0.85rem">
                        <span style="color:var(--text-muted)">Scraped Posts</span>
                        <span style="color:var(--cyan);font-weight:600"><?= $scraped ?></span>
                    </div>
                </div>
            </div>

            <!-- Log output -->
            <div class="col-lg-7">
                <?php if ($runSource): ?>
                <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">SCRAPE LOG</h6>
                <div class="log-box">
                    <?php foreach ($log as $line): ?>
                    <div><?= $line ?></div>
                    <?php endforeach; ?>
                </div>
                <?php if ($imported > 0): ?>
                <div class="d-flex gap-3 mt-3">
                    <a href="<?= SITE_URL ?>/admin/posts.php" class="btn-glow">
                        <i class="fas fa-list me-1"></i> View All Posts
                    </a>
                    <a href="<?= SITE_URL ?>/tutorials.php" target="_blank" class="btn-hero-secondary" style="font-size:0.85rem">
                        <i class="fas fa-eye me-1"></i> Preview on Site
                    </a>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="glass-card p-5 text-center" style="border-style:dashed">
                    <i class="fas fa-robot fa-4x mb-3 d-block" style="color:var(--cyan);opacity:0.3"></i>
                    <h5 style="color:var(--text-muted)">Select a source to begin scraping</h5>
                    <p style="color:var(--text-muted);font-size:0.85rem">
                        The scraper will fetch post listings, visit each post,
                        extract title + content + images, and save them to your database.
                    </p>
                    <div style="font-size:0.78rem;color:var(--text-muted);margin-top:1rem">
                        <i class="fas fa-clock me-1"></i> Allow 2–5 minutes per source (rate-limited to avoid bans)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Instructions -->
        <div class="glass-card p-4 mt-4">
            <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--orange);margin-bottom:1rem">
                <i class="fas fa-info-circle me-2"></i>HOW IT WORKS
            </h6>
            <div class="row g-3">
                <?php
                $steps = [
                    ['1','fas fa-search','Discover Posts', 'Fetches the listing page and finds all post links using multiple CSS/XPath selectors.'],
                    ['2','fas fa-download','Fetch Content', 'Visits each post individually, extracts title, full HTML content, images, and date.'],
                    ['3','fas fa-image','Save Images', 'Downloads featured images to your uploads folder for self-hosted serving.'],
                    ['4','fas fa-database','Store in DB', 'Saves cleaned content to your posts table with source attribution link.'],
                ];
                foreach ($steps as $step): ?>
                <div class="col-md-3">
                    <div style="text-align:center;padding:1rem">
                        <div style="width:40px;height:40px;border-radius:50%;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;color:var(--cyan)">
                            <i class="<?= $step[1] ?>"></i>
                        </div>
                        <div style="font-weight:600;font-size:0.85rem;margin-bottom:4px"><?= $step[2] ?></div>
                        <div style="font-size:0.78rem;color:var(--text-muted)"><?= $step[3] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="alert-glass mt-3" style="border-color:rgba(255,45,120,0.3);background:rgba(255,45,120,0.05)">
            <i class="fas fa-trash-alt" style="color:var(--pink)"></i>
            <span><strong>Security:</strong> Delete <code>/scraper/scrape.php</code> from your server after importing to prevent unauthorized access.</span>
        </div>
    </div>
</div>

<script>
function confirmScrape(el) {
    if (!confirm('Start scraping from:\n' + el.href.split('source=')[1] + '\n\nThis may take several minutes. Do not close the browser.')) return false;
    el.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Scraping...';
    el.style.opacity = '0.7';
    return true;
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
