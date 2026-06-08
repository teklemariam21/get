#!/usr/bin/env php
<?php
/**
 * CLI Tutorial Scraper for Hitechcomputer
 * Run from terminal: php scraper/run-scraper.php [blogger|mcr_tutorial|mcr_solution|all]
 *
 * Examples:
 *   php scraper/run-scraper.php all
 *   php scraper/run-scraper.php blogger
 *   php scraper/run-scraper.php mcr_tutorial
 */

define('CLI_MODE', true);

// Bootstrap without session
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hitechcomputer');
define('SITE_URL', 'http://localhost/get');
define('UPLOAD_DIR', __DIR__ . '/../assets/images/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/images/uploads/');

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}

set_time_limit(0);

$SOURCES = [
    'blogger' => [
        'name'      => 'Cell Phone Repair Tutorials (Blogger)',
        'base_url'  => 'https://cellphonerepairtutorials.blogspot.com',
        'start_url' => 'https://cellphonerepairtutorials.blogspot.com/?m=1',
        'type'      => 'blogger',
        'category'  => 'tutorial',
        'max_pages' => 20,
    ],
    'mcr_tutorial' => [
        'name'      => 'Mobile Repairing – Tutorials',
        'base_url'  => 'https://www.mobilecellphonerepairing.com',
        'start_url' => 'https://www.mobilecellphonerepairing.com/category/mobile-phone-repairing-tutorial',
        'type'      => 'wordpress',
        'category'  => 'tutorial',
        'max_pages' => 20,
    ],
    'mcr_solution' => [
        'name'      => 'Mobile Repairing – Problem Solutions',
        'base_url'  => 'https://www.mobilecellphonerepairing.com',
        'start_url' => 'https://www.mobilecellphonerepairing.com/mobile-phone-problem-solution',
        'type'      => 'wordpress',
        'category'  => 'trick',
        'max_pages' => 20,
    ],
];

$target = $argv[1] ?? 'all';

function cliLog(string $msg): void {
    echo strip_tags($msg) . "\n";
}

function fetchURL(string $url): string {
    static $delays = 0;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
        ],
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_COOKIEJAR  => sys_get_temp_dir() . '/scraper_cookies.txt',
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . '/scraper_cookies.txt',
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) { cliLog("  cURL error: $err"); return ''; }
    if ($code === 429) { cliLog("  Rate limited (429). Sleeping 30s..."); sleep(30); }
    if ($code === 403) { cliLog("  403 Forbidden: $url"); return ''; }
    return ($code >= 200 && $code < 400) ? (string)$html : '';
}

function getDom(string $html): DOMDocument {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();
    return $doc;
}

function downloadImage(string $url): ?string {
    if (!$url || strpos($url, 'http') !== 0) return null;
    if (preg_match('/\b(1x1|pixel|blank|spacer|noimage)\b/i', $url)) return null;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible)',
    ]);
    $data = curl_exec($ch);
    $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$data || !preg_match('#image/(jpeg|png|webp|gif|jpg)#i', $mime, $m)) return null;
    $ext  = ($m[1] === 'jpg') ? 'jpeg' : strtolower($m[1]);
    $name = 'scrape_' . md5($url) . '.' . $ext;
    $path = UPLOAD_DIR . $name;
    if (!file_exists($path)) file_put_contents($path, $data);
    return $name;
}

function cleanContent(string $html, string $baseUrl): string {
    $html = preg_replace_callback('/(?:href|src)=["\']([^"\']*)["\']/', function($m) use ($baseUrl) {
        $url = $m[1];
        if ($url && strpos($url, 'http') !== 0 && strpos($url, '//') !== 0 && strpos($url, '#') !== 0 && strpos($url, 'data:') !== 0) {
            $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
        }
        return str_replace($m[1], $url, $m[0]);
    }, $html);
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
    $html = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $html);
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    $html = preg_replace('/\s{3,}/', "\n", $html);
    return trim($html);
}

function scrapeAndSavePost(string $url, array $source, PDO $pdo): bool {
    $urlHash = md5($url);
    $exists = $pdo->prepare("SELECT id FROM posts WHERE tags LIKE ?");
    $exists->execute(["%src_$urlHash%"]);
    if ($exists->fetch()) { cliLog("  ⏭ Already exists: $url"); return false; }

    $html = fetchURL($url);
    if (!$html) return false;

    $doc = getDom($html);
    $xp  = new DOMXPath($doc);

    // Title
    $title = '';
    foreach (["//h1[contains(@class,'entry-title')]", "//h1[contains(@class,'post-title')]",
              "//meta[@property='og:title']/@content", "//h1", "//title"] as $q) {
        $nl = $xp->query($q);
        if ($nl && $nl->length) {
            $n = $nl->item(0);
            $t = $n->nodeType === XML_ATTRIBUTE_NODE ? $n->value : $n->textContent;
            $t = trim(preg_replace('/\s+/', ' ', $t));
            if (strlen($t) > 5) { $title = $t; break; }
        }
    }
    if (!$title) { cliLog("  ✗ No title: $url"); return false; }

    // Content
    $content = '';
    foreach (["//div[contains(@class,'post-body')]", "//div[contains(@class,'entry-content')]",
              "//div[@class='post-content']", "//div[contains(@class,'td-post-content')]",
              "//article", "//div[@id='postContent']"] as $q) {
        $nl = $xp->query($q);
        if ($nl && $nl->length) {
            $c = $doc->saveHTML($nl->item(0));
            if (strlen(strip_tags($c)) > 100) { $content = $c; break; }
        }
    }
    if (!$content || strlen(strip_tags($content)) < 80) {
        cliLog("  ✗ Content too short: $url"); return false;
    }
    $content = cleanContent($content, $source['base_url']);

    // Date
    $pubDate = null;
    foreach (["//meta[@property='article:published_time']/@content",
              "//time[@class='published']/@datetime", "//time/@datetime"] as $q) {
        $nl = $xp->query($q);
        if ($nl && $nl->length) {
            $v = $nl->item(0)->nodeValue ?? $nl->item(0)->value ?? '';
            if ($v) { $pubDate = date('Y-m-d H:i:s', strtotime($v)); break; }
        }
    }

    // Featured image
    $imageName = null;
    foreach (["//meta[@property='og:image']/@content",
              "//div[contains(@class,'post-body')]//img[1]/@src",
              "//div[contains(@class,'entry-content')]//img[1]/@src",
              "//article//img[1]/@src"] as $q) {
        $nl = $xp->query($q);
        if ($nl && $nl->length) {
            $v = $nl->item(0)->nodeValue ?? $nl->item(0)->value ?? '';
            if ($v && strpos($v, 'http') === 0 && !preg_match('/\b(avatar|logo|icon)\b/i', $v)) {
                $imageName = downloadImage($v);
                break;
            }
        }
    }

    $excerpt = substr(trim(preg_replace('/\s+/', ' ', strip_tags($content))), 0, 200);
    $slug    = slugify($title);
    $base    = $slug; $i = 1;
    while ($pdo->query("SELECT COUNT(*) FROM posts WHERE slug = " . $pdo->quote($slug))->fetchColumn() > 0) {
        $slug = $base . '-' . $i++;
    }

    $sourceNote = '<p class="post-source" style="font-size:0.75rem;color:#888;border-left:3px solid #00d4ff;padding-left:10px;margin-bottom:1.5rem">Source: <a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($url) . '</a></p>';

    try {
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, excerpt, content, category, tags, image, status, featured, created_at, updated_at) VALUES (?,?,?,?,?,?,?,'published',0,?,NOW())");
        $stmt->execute([$title, $slug, $excerpt, $sourceNote . $content, $source['category'],
                        'mobile repair,phone repair,src_' . $urlHash, $imageName, $pubDate ?? date('Y-m-d H:i:s')]);
        cliLog("  ✓ Saved: $title");
        return true;
    } catch (Exception $e) {
        cliLog("  ✗ DB error: " . $e->getMessage());
        return false;
    }
}

function scrapeSource(array $source, PDO $pdo): int {
    $count   = 0;
    $nextUrl = $source['start_url'];
    $pages   = 0;

    while ($nextUrl && $pages < $source['max_pages']) {
        $pages++;
        cliLog("\n📄 Page $pages: $nextUrl");
        $html = fetchURL($nextUrl);
        if (!$html) { cliLog("Empty response."); break; }

        $doc = getDom($html);
        $xp  = new DOMXPath($doc);

        $postLinks = [];
        $selectors = $source['type'] === 'blogger' ? [
            "//h3[contains(@class,'post-title')]//a",
            "//h2[contains(@class,'post-title')]//a",
            "//a[contains(@class,'post-title')]",
            "//div[contains(@class,'post-outer')]//h3/a",
            "//div[contains(@class,'post')]//h3/a",
        ] : [
            "//article//h2[contains(@class,'entry-title')]//a",
            "//article//h3[contains(@class,'entry-title')]//a",
            "//h2[contains(@class,'entry-title')]//a",
            "//div[contains(@class,'post')]//h2/a",
        ];

        foreach ($selectors as $q) {
            $nl = $xp->query($q);
            if ($nl && $nl->length > 0) {
                foreach ($nl as $n) {
                    $h = $n->getAttribute('href');
                    if ($h && strpos($h, '#') === false) $postLinks[] = $h;
                }
                if (count($postLinks)) break;
            }
        }
        $postLinks = array_unique($postLinks);
        cliLog("  Found " . count($postLinks) . " posts.");

        foreach ($postLinks as $link) {
            if (scrapeAndSavePost($link, $source, $pdo)) $count++;
            usleep(rand(700000, 1200000)); // 0.7–1.2s random delay
        }

        // Next page
        $nextUrl = '';
        $nextSel = $source['type'] === 'blogger' ? [
            "//a[contains(@class,'blog-pager-older-link')]",
            "//a[@id='Blog1_blog-pager-older-link']",
        ] : [
            "//a[contains(@class,'next') and contains(@class,'page-numbers')]",
            "//a[contains(@rel,'next')]",
        ];
        foreach ($nextSel as $q) {
            $nl = $xp->query($q);
            if ($nl && $nl->length) {
                $h = $nl->item(0)->getAttribute('href');
                if ($h) { $nextUrl = $h; break; }
            }
        }
        if ($nextUrl) sleep(rand(2, 4)); // 2–4s between pages
    }
    return $count;
}

// ── Main ──
$toRun = ($target === 'all') ? array_keys($SOURCES) : [$target];

foreach ($toRun as $key) {
    if (!isset($SOURCES[$key])) { cliLog("Unknown source: $key"); continue; }
    $src = $SOURCES[$key];
    cliLog("\n" . str_repeat('=', 60));
    cliLog("SOURCE: {$src['name']}");
    cliLog(str_repeat('=', 60));
    $n = scrapeSource($src, $pdo);
    cliLog("\n✅ Imported $n new posts from {$src['name']}");
}

cliLog("\n🎉 All done!");
