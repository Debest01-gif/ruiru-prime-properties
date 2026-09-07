<?php
/**
 * Database Connection
 * Ruiru Prime Properties
 */

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$dbName = getenv('DB_NAME') ?: 'ruiru_realestate';
$dbPort = getenv('DB_PORT') ?: '3306';

// Support full URL format commonly provided by cloud platforms like Render
$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
if ($dbUrl) {
    $parts = parse_url($dbUrl);
    if (!empty($parts['host'])) $dbHost = $parts['host'];
    if (!empty($parts['user'])) $dbUser = $parts['user'];
    if (isset($parts['pass']))  $dbPass = $parts['pass'];
    if (!empty($parts['path'])) $dbName = ltrim($parts['path'], '/');
    if (!empty($parts['port'])) $dbPort = $parts['port'];
}

define('DB_HOST', $dbHost);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);
define('DB_PORT', $dbPort);
define('DB_CHARSET', 'utf8mb4');

// Dynamic Site URL detection (works on Apache, PHP dev server, and live cPanel/hosting)
if (!defined('SITE_URL')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
        $appRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));
        $subDir = '';
        if ($docRoot && str_starts_with($appRoot, $docRoot)) {
            $subDir = substr($appRoot, strlen($docRoot));
        }
        define('SITE_URL', rtrim($protocol . '://' . $host . $subDir, '/'));
    } else {
        define('SITE_URL', 'http://localhost/real%20estate');
    }
}
define('UPLOADS_PATH', __DIR__ . '/../uploads/');
define('UPLOADS_URL', SITE_URL . '/uploads/');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:40px;background:#1a0a0a;color:#ff6b6b;text-align:center;">
        <h2>⚠️ Database Connection Error</h2>
        <p>Could not connect to the database. Please check your settings in <code>includes/db.php</code></p>
        <p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>
        <p>Make sure you have imported <code>database/schema.sql</code> into phpMyAdmin.</p>
    </div>');
}

// Load site settings into a global array
function getSettings(PDO $pdo): array {
    static $settings = null;
    if ($settings === null) {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

$GLOBALS['settings'] = getSettings($pdo);

function setting(string $key, string $default = ''): string {
    return $GLOBALS['settings'][$key] ?? $default;
}
