<?php

namespace App\Models;

use App\Config;
use PDO;

class Log {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getDBConnection();
    }

    public function getAdminLogs($limit = 5) {
        $stmt = $this->pdo->prepare("SELECT l.*, u_admin.pseudo as admin_pseudo, u_cible.pseudo as cible_pseudo FROM logs_actions l LEFT JOIN utilisateurs u_admin ON l.id_admin = u_admin.id LEFT JOIN utilisateurs u_cible ON l.id_cible = u_cible.id ORDER BY l.date_action DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createActionLog($adminId, $targetId, $action, $details) {
        $stmt = $this->pdo->prepare("INSERT INTO logs_actions (id_admin, id_cible, action, details, adresse_ip, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$adminId, $targetId, $action, $details, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    }

    public function createLoginLog($pseudo, $status) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];
        
        // Géolocalisation via ip-api.com
        $location = $this->getIPLocation($ip);

        $stmt = $this->pdo->prepare("INSERT INTO logs_connexions (pseudo_tente, statut, adresse_ip, user_agent, location) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$pseudo, $status, $ip, $ua, $location]);
    }

    public function getLoginLogs($limit = 10) {
        $stmt = $this->pdo->prepare("SELECT * FROM logs_connexions ORDER BY date_tentative DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getIPLocation($ip) {
        // Pour les tests en local (127.0.0.1), l'API ne retournera rien
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return "Localhost";
        }

        try {
            // Utilisation d'un timeout court pour ne pas bloquer le login si l'API est lente
            $ctx = stream_context_create(['http' => ['timeout' => 2]]);
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city", false, $ctx);
            if ($response) {
                $data = json_decode($response, true);
                if ($data && $data['status'] === 'success') {
                    return $data['country'] . ", " . $data['city'];
                }
            }
        } catch (\Exception $e) {
            // Ignorer l'erreur et retourner inconnu
        }
        return "Unknown";
    }
}
