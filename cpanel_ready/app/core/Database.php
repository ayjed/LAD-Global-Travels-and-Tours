<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO Database connector singleton for MySQL 8+ / MariaDB.
 * Enforces prepared statements, exception mode, associative array fetches, and UTF-8 charset.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Get the active PDO instance or initialize a new connection.
     *
     * @param array|null $config Optional custom connection parameters
     * @return PDO
     * @throws RuntimeException If database connection fails
     */
    public static function getConnection(?array $config = null): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = $config['host'] ?? Env::get('DB_HOST', '127.0.0.1');
        $port = (int)($config['port'] ?? Env::get('DB_PORT', 3306));
        $database = $config['database'] ?? Env::get('DB_DATABASE', 'lad_global_db');
        $username = $config['username'] ?? Env::get('DB_USERNAME', 'root');
        $password = $config['password'] ?? Env::get('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
            return self::$instance;
        } catch (PDOException $e) {
            // Log connection error privately without exposing credentials
            error_log('[Database Error] Connection failed: ' . $e->getMessage());

            $debug = (bool)Env::get('APP_DEBUG', false);
            if ($debug) {
                throw new RuntimeException("Database connection error: " . $e->getMessage(), (int)$e->getCode(), $e);
            }

            throw new RuntimeException("Unable to connect to the database. Please try again later.", 500);
        }
    }

    /**
     * Test whether the database connection can be established without throwing an uncaught exception.
     *
     * @param array|null $config
     * @return array [bool $success, string $message]
     */
    public static function testConnection(?array $config = null): array
    {
        try {
            self::getConnection($config);
            return [true, 'Database connection successful.'];
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }
    }

    /**
     * Reset the cached instance (useful for unit/smoke testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
