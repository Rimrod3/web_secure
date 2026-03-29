<?php

namespace App\Models;

use App\Config;
use PDO;

class Ticket {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getDBConnection();
    }

    public function create($userId, $message) {
        $stmt = $this->pdo->prepare("INSERT INTO tickets (id_utilisateur, message) VALUES (?, ?)");
        return $stmt->execute([$userId, $message]);
    }

    public function getAllWithUser() {
        return $this->pdo->query("SELECT t.*, u.pseudo FROM tickets t JOIN utilisateurs u ON t.id_utilisateur = u.id ORDER BY t.date_envoi DESC")->fetchAll();
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE id_utilisateur = ? ORDER BY date_envoi DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
