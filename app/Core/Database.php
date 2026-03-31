<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;


final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = self::$config['host'] ?? '127.0.0.1';
        $port = (int) (self::$config['port'] ?? 3306);
        $database = self::$config['database'] ?? '';
        $charset = self::$config['charset'] ?? 'utf8mb4';
        $username = self::$config['username'] ?? 'root';
        $password = self::$config['password'] ?? '';

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

        try {
            self::$pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo '<h1>Database connection failed</h1>';
            echo '<p>Please check <code>config/database.php</code>.</p>';
            if ((bool) config('app.debug', false)) {
                echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
            }
            exit;
        }

        return self::$pdo;
    }
}
