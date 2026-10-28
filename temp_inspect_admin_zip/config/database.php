<?php
// ==========================================================================
// TRUENORTH GROUP — DATABASE PDO CONNECTOR (WITH SILENT STANDALONE FALLBACK)
// ==========================================================================

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo = null;

    private function __construct() {
        $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
        $port = defined('DB_PORT') ? (int)DB_PORT : 3306;
        $name = defined('DB_NAME') ? DB_NAME : 'truenorth_db';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';

        // Normalize localhost to 127.0.0.1 on Windows to bypass IPv6 DNS resolution lag
        if ($host === 'localhost') {
            $host = '127.0.0.1';
        }

        // Fast non-blocking socket probe (max 0.05s) before full PDO handshake
        $socket = @fsockopen($host, $port, $errno, $errstr, 0.05);
        if (!$socket) {
            $this->pdo = null;
            return;
        }
        fclose($socket);

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 1,
            ];
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function isConnected() {
        return $this->pdo !== null;
    }
}
