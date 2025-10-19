<?php
if (!defined('APP_ACCESS')) die('Direct access not permitted');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ppnitang_nis');
define('DB_USER', 'ppnitang');
define('DB_PASS', 'ppnitang_nis');

// App Configuration
define('APP_NAME', 'Nursing System');
define('BASE_URL', 'http://localhost/nursingsystem/public');

// Get Database Connection (Singleton)
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // Tampilkan error asli (debug mode)
            echo "<pre style='color:red; font-family:monospace; background:#fff0f0; padding:10px; border:1px solid #faa;'>";
            echo "❌ <b>Database Connection Failed:</b><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</pre>";
            exit;
        }
    }
    return $pdo;
}
