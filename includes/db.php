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

// Helper to establish SQLite connection with MySQL-compatibility functions
function createSqliteConnection(string $sqliteFile): PDO {
    $isNew = !file_exists($sqliteFile);
    $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec("PRAGMA foreign_keys = ON;");
    $pdo->exec("PRAGMA journal_mode = WAL;");

    // Register custom SQL functions for 100% MySQL query compatibility
    $pdo->sqliteCreateFunction('RAND', function() {
        return (float)mt_rand() / (float)mt_getrandmax();
    });
    $pdo->sqliteCreateFunction('IF', function($cond, $trueVal, $falseVal) {
        return $cond ? $trueVal : $falseVal;
    });
    $pdo->sqliteCreateFunction('NOW', function() {
        return date('Y-m-d H:i:s');
    });

    if ($isNew) {
        require_once __DIR__ . '/../database/init_sqlite.php';
    }

    return $pdo;
}

$dbDriver = getenv('DB_DRIVER') ?: 'auto';
$sqliteFile = __DIR__ . '/../database/ruiru_realestate.sqlite';

if ($dbDriver === 'sqlite') {
    $pdo = createSqliteConnection($sqliteFile);
} else {
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
        // In local development or when MySQL is offline, fall back seamlessly to SQLite
        if (file_exists($sqliteFile) || extension_loaded('pdo_sqlite')) {
            $pdo = createSqliteConnection($sqliteFile);
        } else {
            die('<div style="font-family:sans-serif;padding:40px;background:#1a0a0a;color:#ff6b6b;text-align:center;">
                <h2>⚠️ Database Connection Error</h2>
                <p>Could not connect to the database. Please check your settings in <code>includes/db.php</code></p>
                <p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>
                <p>Make sure you have imported <code>database/schema.sql</code> into phpMyAdmin or have SQLite enabled.</p>
            </div>');
        }
    }
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
