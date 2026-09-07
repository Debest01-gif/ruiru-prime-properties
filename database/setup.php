<?php
/**
 * Database Auto-Installer / Migrator
 * Imports schema.sql into MySQL
 */
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Reading schema.sql...\n";
    $sqlFile = __DIR__ . '/schema.sql';
    if (!file_exists($sqlFile)) {
        die("Error: schema.sql not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);

    // Split SQL by semicolons, accounting for multi-query execution
    echo "Executing database initialization...\n";
    $pdo->exec($sql);

    echo "SUCCESS: Database ruiru_realestate created and seeded successfully!\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
