<?php
// config/database.php
// ============================================================
// Konfigurasi koneksi database MySQL
// Ganti nilai di bawah sesuai environment kamu
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_artikel');
define('DB_USER', 'root');      // Ganti dengan username MySQL kamu
define('DB_PASS', '');          // Ganti dengan password MySQL kamu
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Di production, jangan tampilkan error detail
                error_log('Database Connection Error: ' . $e->getMessage());
                http_response_code(503);
                die(json_encode(['error' => 'Service Unavailable']));
            }
        }
        return self::$instance;
    }

    // Mencegah clone dan unserialize
    private function __clone() {}
    public function __wakeup() {}
}
