<?php
// =============================================
// Habesha Connect - Configuration
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'habesha_connect');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'Habesha Connect');
define('SITE_URL', 'http://localhost/habesha-connect');
define('SITE_EMAIL', 'admin@habeshaconnect.com');
define('UPLOAD_PATH', __DIR__ . '/../uploads/profiles/');
define('UPLOAD_URL', SITE_URL . '/uploads/profiles/');
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Session
define('SESSION_NAME', 'habesha_session');
define('SESSION_LIFETIME', 86400 * 30);

// Timezone (Ethiopia is UTC+3)
date_default_timezone_set('Africa/Addis_Ababa');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =============================================
// Database Connection (PDO)
// =============================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
                <h2 style="color:#dc3545;">Database Connection Error</h2>
                <p>Please check your database configuration.</p>
                <small>' . htmlspecialchars($e->getMessage()) . '</small>
            </div>');
        }
    }
    return $pdo;
}

// =============================================
// Session Start
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Ethiopian Cities & Regions
define('ETHIOPIAN_CITIES', [
    'Addis Ababa'  => 'Addis Ababa',
    'Dire Dawa'    => 'Dire Dawa',
    'Mekelle'      => 'Tigray',
    'Gondar'       => 'Amhara',
    'Bahir Dar'    => 'Amhara',
    'Adama'        => 'Oromia',
    'Hawassa'      => 'Sidama',
    'Dessie'       => 'Amhara',
    'Jimma'        => 'Oromia',
    'Harar'        => 'Harari',
    'Jijiga'       => 'Somali',
    'Shashamane'   => 'Oromia',
    'Nekemte'      => 'Oromia',
    'Arba Minch'   => 'SNNP',
    'Wolaita Sodo' => 'SNNP',
    'Dilla'        => 'SNNP',
    'Debre Birhan' => 'Amhara',
    'Debre Markos' => 'Amhara',
    'Aksum'        => 'Tigray',
    'Adigrat'      => 'Tigray',
    'Lalibela'     => 'Amhara',
    'Asosa'        => 'Benishangul-Gumuz',
    'Gambela'      => 'Gambela',
    'Abroad'       => 'Diaspora',
]);

define('ETHIOPIAN_ETHNICITIES', [
    'Oromo', 'Amhara', 'Tigrayan', 'Somali', 'Sidama', 'Afar',
    'Welayta', 'Gurage', 'Hadiya', 'Gamo', 'Omo', 'Agew',
    'Silte', 'Bench', 'Kafa', 'Dawro', 'Ari', 'Nuer',
    'Anuak', 'Majang', 'Other',
]);

define('ETHIOPIAN_RELIGIONS', [
    'Orthodox Christian', 'Muslim', 'Protestant', 'Catholic',
    'Wakefeta', 'Judaism', 'Traditional', 'Other',
]);

define('EDUCATION_LEVELS', [
    'High School', 'Diploma', 'Bachelor Degree',
    'Master Degree', 'PhD', 'Professional Degree', 'Other',
]);

define('BODY_TYPES', ['Slim', 'Athletic', 'Average', 'Curvy', 'Plus Size']);
define('HAIR_TYPES', ['Straight', 'Wavy', 'Curly', 'Coily', 'Bald']);
define('EYE_COLORS', ['Brown', 'Dark Brown', 'Black', 'Hazel', 'Other']);
define('SKIN_TONES', ['Very Light', 'Light', 'Medium', 'Olive', 'Brown', 'Dark Brown', 'Very Dark']);
