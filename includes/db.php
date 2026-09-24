<?php
/**
 * Database Connection
 * Ruiru Prime Properties
 */

$envHost = getenv('DB_HOST');
$envUser = getenv('DB_USER');
$envPass = getenv('DB_PASS');
$envName = getenv('DB_NAME');
$envPort = getenv('DB_PORT');
$envUrl  = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');

if ($envUrl) {
    $parts = parse_url($envUrl);
    if (!empty($parts['host'])) $envHost = $parts['host'];
    if (!empty($parts['user'])) $envUser = $parts['user'];
    if (isset($parts['pass']))  $envPass = $parts['pass'];
    if (!empty($parts['path'])) $envName = ltrim($parts['path'], '/');
    if (!empty($parts['port'])) $envPort = $parts['port'];
}

$hasMysqlConfig = !empty($envHost);

define('DB_HOST', $hasMysqlConfig ? $envHost : 'localhost');
define('DB_USER', $hasMysqlConfig ? ($envUser ?: 'root') : 'root');
define('DB_PASS', $hasMysqlConfig ? ($envPass !== false ? $envPass : '') : '');
define('DB_NAME', $hasMysqlConfig ? ($envName ?: 'ruiru_realestate') : 'ruiru_realestate');
define('DB_PORT', $hasMysqlConfig ? ($envPort ?: '3306') : '3306');
define('DB_CHARSET', 'utf8mb4');

// Dynamic Site URL detection (works on Apache, PHP dev server, Docker, Render, cPanel)
if (!defined('SITE_URL')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $protocol = $isHttps ? 'https' : 'http';
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
    $dir = dirname($sqliteFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $isNew = !file_exists($sqliteFile) || filesize($sqliteFile) === 0;
    $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,
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
        ob_start();
        require_once __DIR__ . '/../database/init_sqlite.php';
        ob_end_clean();
    }

    return $pdo;
}

$dbDriver = getenv('DB_DRIVER') ?: ($hasMysqlConfig ? 'mysql' : 'sqlite');
$sqliteFile = __DIR__ . '/../database/ruiru_realestate.sqlite';

if ($dbDriver === 'sqlite' || (!$hasMysqlConfig && $dbDriver !== 'mysql')) {
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
                PDO::ATTR_TIMEOUT            => 3,
            ]
        );
    } catch (PDOException $e) {
        // Fall back seamlessly to SQLite
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
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings;
}

$GLOBALS['settings'] = getSettings($pdo);

function setting(string $key, string $default = ''): string {
    return $GLOBALS['settings'][$key] ?? $default;
}
