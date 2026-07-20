<?php
/**
 * Database — PDO singleton
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                self::$instance->exec("SET time_zone = '+06:00'");
            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    die('<pre style="color:red">Database connection failed: ' . $e->getMessage() . '</pre>');
                }
                die('Service temporarily unavailable. Please try again later.');
            }
        }
        return self::$instance;
    }

    // Prevent cloning / unserialization
    private function __clone() {}
    public function __wakeup() {}
}
