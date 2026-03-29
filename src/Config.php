<?php

namespace App;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Config {
    private static $pdo = null;

    public static function loadEnv() {
        $path = realpath(__DIR__ . '/../');
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->load();
    }

    public static function getSecretKey() {
        $secret_key = $_SERVER['JWT_SECRET_KEY'] ?? $_ENV['JWT_SECRET_KEY'] ?? null;
        if ($secret_key === null) {
            die("Security error: JWT secret key is not configured. Please set it in the .env file.");
        }
        return $secret_key;
    }

    public static function getDBConnection() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? null;
        $db   = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? null;
        $user = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? null;
        $pass = $_SERVER['DB_PASS'] ?? $_ENV['DB_PASS'] ?? null;

        if (!$host || !$db || !$user) {
            die("Config error: Incomplete database credentials. Check .env (HOST: $host, DB: $db, USER: $user)");
        }

        $dsn_host = ($host === 'localhost') ? '127.0.0.1' : $host;
        $dsn = "mysql:host=$dsn_host;dbname=$db;charset=utf8mb4";

        try {
            $options = [
                PDO::MYSQL_ATTR_SSL_CA   => realpath(__DIR__ . '/../server.crt'),
                PDO::MYSQL_ATTR_SSL_CERT => realpath(__DIR__ . '/../mysql-client.crt'),
                PDO::MYSQL_ATTR_SSL_KEY  => realpath(__DIR__ . '/../mysql-client.key'),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            self::$pdo = new PDO($dsn, $user, $pass, $options);
            return self::$pdo;
        } catch (PDOException $e) {
            error_log("PDO Connection Error: " . $e->getMessage());
            die("Internal server error: Could not connect to the database.");
        }
    }

    public static function setSecurityHeaders() {
        /*
         * CSP notes:
         * - 'unsafe-inline' kept for style/script because the project uses inline styles
         *   and inline event handlers in some views (admin dashboard).
         * - Google Fonts REMOVED from login.css — no external font sources needed.
         *   If you add Google Fonts back, add fonts.googleapis.com and fonts.gstatic.com here.
         * - img-src includes 'self' data: to allow base64 avatars if needed.
         */
        header("Content-Security-Policy: "
            . "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline'; "
            . "style-src 'self' 'unsafe-inline'; "
            . "img-src 'self' data:; "
            . "font-src 'self'; "
            . "connect-src 'self'; "
            . "frame-ancestors 'none';"
        );
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}