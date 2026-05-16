<?php

class Connection extends PDO
{
    private static ?Connection $instance = null;

    private function __construct()
    {
        $host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'database';
        $port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'RSI';
        $user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'rsi_user';
        $password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: 'rsi_password';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            parent::__construct($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            http_response_code(500);
            exit('Database connection error.');
        }
    }

    public static function getInstance(): Connection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new Exception("Cannot unserialize singleton");
    }
}