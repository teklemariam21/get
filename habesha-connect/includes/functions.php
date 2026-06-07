<?php
require_once __DIR__ . '/config.php';

// =============================================
// AUTH HELPERS
// =============================================
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1 AND is_banned = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            // Update last active every 5 minutes
            if (empty($_SESSION['last_active_update']) || time() - $_SESSION['last_active_update'] > 300) {
                $db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$user['id']]);
                $_SESSION['last_active_update'] = time();
            }
        }
    }
    return $user ?: null;
}

function getUserById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// =============================================
// PROFILE & PHOTO HELPERS
// =============================================
function getProfilePhoto(?string $filename, string $gender = 'male'): string {
    if ($filename && file_exists(UPLOAD_PATH . $filename)) {
        return UPLOAD_URL . htmlspecialchars($filename);
    }
    return SITE_URL . '/assets/images/default-' . $gender . '.svg';
}

function getAge(string $birthdate): int {
    return (int) date_diff(date_create($birthdate), date_create('today'))->y;
}

function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function isOnline(?string $lastActive): bool {
    if (!$lastActive) return false;
    return (time() - strtotime($lastActive)) < 600; // 10 minutes
}

function uploadPhoto(array $file, int $userId): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_PHOTO_SIZE) return false;
    if (!in_array($file['type'], ALLOWED_PHOTO_TYPES)) return false;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $userId . '_' . uniqid() . '.' . strtolower($ext);
    $dest     = UPLOAD_PATH . $filename;

    if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // Resize to max 800px wide if GD available
        if (extension_loaded('gd') && in_array($file['type'], ['image/jpeg', 'image/png'])) {
            resizeImage($dest, 800);
        }
        return $filename;
    }
    return false;
}

function resizeImage(string $path, int $maxWidth): void {
    [$w, $h, $type] = getimagesize($path);
    if ($w <= $maxWidth) return;
    $ratio  = $maxWidth / $w;
    $newH   = (int)($h * $ratio);
    $dst    = imagecreatetruecolor($maxWidth, $newH);
    $src    = ($type === IMAGETYPE_PNG) ? imagecreatefrompng($path) : imagecreatefromjpeg($path);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $newH, $w, $h);
    if ($type === IMAGETYPE_PNG) imagepng($dst, $path, 8);
    else imagejpeg($dst, $path, 85);
    imagedestroy($src);
    imagedestroy($dst);
}

// =============================================
// LIKE / MATCH HELPERS
// =============================================
function hasLiked(int $fromId, int $toId): bool {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM likes WHERE from_user_id = ? AND to_user_id = ?");
    $stmt->execute([$fromId, $toId]);
    return (bool) $stmt->fetch();
}

function isMatch(int $userId1, int $userId2): bool {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT id FROM matches WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)"
    );
    $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
    return (bool) $stmt->fetch();
}

function toggleLike(int $fromId, int $toId): array {
    $db = getDB();
    if (hasLiked($fromId, $toId)) {
        $db->prepare("DELETE FROM likes WHERE from_user_id = ? AND to_user_id = ?")->execute([$fromId, $toId]);
        // Remove match if exists
        $db->prepare("DELETE FROM matches WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)")
           ->execute([$fromId, $toId, $toId, $fromId]);
        return ['action' => 'unliked', 'is_match' => false];
    } else {
        $db->prepare("INSERT IGNORE INTO likes (from_user_id, to_user_id) VALUES (?,?)")->execute([$fromId, $toId]);
        addNotification($toId, 'like', $fromId, 'Someone liked your profile!', '/profile.php?id=' . $fromId);
        // Check for mutual like → create match
        if (hasLiked($toId, $fromId)) {
            $uid1 = min($fromId, $toId);
            $uid2 = max($fromId, $toId);
            $db->prepare("INSERT IGNORE INTO matches (user1_id, user2_id) VALUES (?,?)")->execute([$uid1, $uid2]);
            addNotification($toId, 'match', $fromId, "You have a new match! 💛", '/messages.php?user=' . $fromId);
            addNotification($fromId, 'match', $toId, "You have a new match! 💛", '/messages.php?user=' . $toId);
            return ['action' => 'liked', 'is_match' => true];
        }
        return ['action' => 'liked', 'is_match' => false];
    }
}

function isBlocked(int $userId, int $targetId): bool {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT id FROM blocked_users WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)"
    );
    $stmt->execute([$userId, $targetId, $targetId, $userId]);
    return (bool) $stmt->fetch();
}

// =============================================
// MESSAGING HELPERS
// =============================================
function getUnreadCount(int $userId): int {
    $db   = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE to_user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function getConversations(int $userId): array {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT m.*,
               u.id AS partner_id, u.username, u.profile_photo, u.gender, u.last_active,
               (SELECT COUNT(*) FROM messages m2 WHERE m2.from_user_id = u.id AND m2.to_user_id = :uid AND m2.is_read = 0) AS unread_count
        FROM messages m
        JOIN users u ON u.id = IF(m.from_user_id = :uid2, m.to_user_id, m.from_user_id)
        WHERE (m.from_user_id = :uid3 OR m.to_user_id = :uid4)
          AND u.is_active = 1
          AND m.id = (
              SELECT MAX(m3.id) FROM messages m3
              WHERE (m3.from_user_id = :uid5 AND m3.to_user_id = u.id)
                 OR (m3.from_user_id = u.id AND m3.to_user_id = :uid6)
          )
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([
        ':uid'  => $userId, ':uid2' => $userId, ':uid3' => $userId,
        ':uid4' => $userId, ':uid5' => $userId, ':uid6' => $userId,
    ]);
    return $stmt->fetchAll();
}

// =============================================
// NOTIFICATION HELPERS
// =============================================
function addNotification(int $userId, string $type, ?int $fromUserId, string $message, string $link = ''): void {
    $db = getDB();
    $db->prepare("INSERT INTO notifications (user_id, type, from_user_id, message, link) VALUES (?,?,?,?,?)")
       ->execute([$userId, $type, $fromUserId, $message, $link]);
}

function getNotifications(int $userId, int $limit = 10): array {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT n.*, u.username, u.profile_photo, u.gender
        FROM notifications n
        LEFT JOIN users u ON u.id = n.from_user_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function getUnreadNotifCount(int $userId): int {
    $db   = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function markNotificationsRead(int $userId): void {
    $db = getDB();
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
}

// =============================================
// SEARCH & BROWSE
// =============================================
function browseUsers(array $filters = [], int $page = 1, int $perPage = 12): array {
    $db     = getDB();
    $offset = ($page - 1) * $perPage;
    $where  = ["u.is_active = 1", "u.is_banned = 0", "u.id != :current_id"];
    $params = [':current_id' => $_SESSION['user_id'] ?? 0];

    if (!empty($filters['gender'])) {
        $where[] = "u.gender = :gender";
        $params[':gender'] = $filters['gender'];
    }
    if (!empty($filters['city'])) {
        $where[] = "u.city = :city";
        $params[':city'] = $filters['city'];
    }
    if (!empty($filters['ethnicity'])) {
        $where[] = "u.ethnicity = :ethnicity";
        $params[':ethnicity'] = $filters['ethnicity'];
    }
    if (!empty($filters['religion'])) {
        $where[] = "u.religion = :religion";
        $params[':religion'] = $filters['religion'];
    }
    if (!empty($filters['min_age'])) {
        $where[] = "TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) >= :min_age";
        $params[':min_age'] = (int) $filters['min_age'];
    }
    if (!empty($filters['max_age'])) {
        $where[] = "TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) <= :max_age";
        $params[':max_age'] = (int) $filters['max_age'];
    }
    if (!empty($filters['keyword'])) {
        $where[] = "(u.username LIKE :kw OR u.about_me LIKE :kw2 OR u.occupation LIKE :kw3)";
        $kw = '%' . $filters['keyword'] . '%';
        $params[':kw'] = $kw; $params[':kw2'] = $kw; $params[':kw3'] = $kw;
    }
    if (!empty($filters['has_photo'])) {
        $where[] = "u.profile_photo IS NOT NULL";
    }
    if (!empty($filters['online_only'])) {
        $where[] = "u.last_active > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
    }

    // Exclude blocked users
    if (!empty($_SESSION['user_id'])) {
        $where[] = "u.id NOT IN (SELECT blocked_id FROM blocked_users WHERE blocker_id = :blocker)
                    AND u.id NOT IN (SELECT blocker_id FROM blocked_users WHERE blocked_id = :blocked)";
        $params[':blocker'] = $_SESSION['user_id'];
        $params[':blocked'] = $_SESSION['user_id'];
    }

    $orderBy = match ($filters['sort'] ?? 'newest') {
        'online'   => "u.last_active DESC",
        'popular'  => "u.profile_views DESC",
        default    => "u.created_at DESC",
    };

    $sql = "SELECT SQL_CALC_FOUND_ROWS u.*,
                   TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) AS age
            FROM users u
            WHERE " . implode(' AND ', $where) . "
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    $total = (int) $db->query("SELECT FOUND_ROWS()")->fetchColumn();

    return ['users' => $users, 'total' => $total, 'pages' => ceil($total / $perPage)];
}

// =============================================
// SECURITY HELPERS
// =============================================
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function getSetting(string $key, string $default = ''): string {
    static $settings = null;
    if ($settings === null) {
        $db   = getDB();
        $rows = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        foreach ($rows as $row) $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings[$key] ?? $default;
}

function recordProfileView(int $viewerId, int $profileId): void {
    if ($viewerId === $profileId) return;
    $db = getDB();
    $db->prepare("INSERT INTO profile_views (viewer_id, profile_id) VALUES (?,?)")->execute([$viewerId, $profileId]);
    $db->prepare("UPDATE users SET profile_views = profile_views + 1 WHERE id = ?")->execute([$profileId]);
}
