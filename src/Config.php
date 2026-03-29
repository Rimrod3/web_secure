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
        // On vérifie dans $_SERVER ou $_ENV
        $secret_key = $_SERVER['JWT_SECRET_KEY'] ?? $_ENV['JWT_SECRET_KEY'] ?? null;
        if ($secret_key === null) {
            die("Erreur de sécurité : La clé secrète JWT n'est pas configurée. Veuillez la définir dans le fichier .env");
        }
        return $secret_key;
    }

    public static function getDBConnection() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // On vérifie dans $_SERVER ou $_ENV
        $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? null;
        $db   = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? null;
        $user = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? null;
        $pass = $_SERVER['DB_PASS'] ?? $_ENV['DB_PASS'] ?? null;

        if (!$host || !$db || !$user) {
            die("Erreur de configuration : Les informations de la base de données sont incomplètes. Vérifiez le fichier .env (HOST: $host, DB: $db, USER: $user)");
        }

        // IMPORTANT : Pour que SSL fonctionne, nous devons passer par TCP (127.0.0.1) 
        // et non par un socket UNIX (comportement par défaut si host=localhost).
        $dsn_host = ($host === 'localhost') ? '127.0.0.1' : $host;
        $dsn = "mysql:host=$dsn_host;dbname=$db;charset=utf8mb4";

        try {
            // Configuration SSL stricte pour la base de données
            $options = [
                PDO::MYSQL_ATTR_SSL_CA   => realpath(__DIR__ . '/../server.crt'),
                PDO::MYSQL_ATTR_SSL_CERT => realpath(__DIR__ . '/../mysql-client.crt'),
                PDO::MYSQL_ATTR_SSL_KEY  => realpath(__DIR__ . '/../mysql-client.key'),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Désactivé si le CN ne match pas exactement l'IP/Hostname local
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            self::$pdo = new PDO($dsn, $user, $pass, $options);
            return self::$pdo;
        } catch (PDOException $e) {
            error_log("PDO Connection Error: " . $e->getMessage());
            die("Erreur interne du serveur. Impossible de se connecter à la base de données.");
        }
    }

    public static function setSecurityHeaders() {
        // On autorise unsafe-inline pour le JS/CSS car le projet en utilise (styles inline et scripts inline de ban)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self'; frame-ancestors 'none';");
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}
