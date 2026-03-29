<?php

namespace App\Models;

use App\Config;
use PDO;

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getDBConnection();
    }

    public function findByPseudo($pseudo) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo = ?");
        $stmt->execute([$pseudo]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll() {
        return $this->pdo->query("SELECT id, pseudo, email, role, est_certifie FROM utilisateurs ORDER BY id ASC")->fetchAll();
    }

    public function create($pseudo, $email, $photo, $passwordHash) {
        $stmt = $this->pdo->prepare("INSERT INTO utilisateurs (pseudo, email, photo_profil, mot_de_passe) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$pseudo, $email, $photo, $passwordHash]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateProfilePicture($id, $photo_path) {
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET photo_profil = ? WHERE id = ?");
        return $stmt->execute([$photo_path, $id]);
    }

    public function updateCertification($id, $state) {
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET est_certifie = ? WHERE id = ?");
        return $stmt->execute([$state, $id]);
    }
}
