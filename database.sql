-- SQL SCHEMA FOR WEB SECURITY PROJECT (BAC-P-CYB-S04-DEVWEBSEC)
-- Authority: ESIEA / Cyber
-- Date: 2026

CREATE DATABASE IF NOT EXISTS web_secure CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE web_secure;

-- 1. Table Utilisateurs
-- Contient les rôles et les états de certification
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    est_certifie BOOLEAN DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table des Tickets
-- Supporte la suppression en cascade si un utilisateur est supprimé
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Logs des Connexions (Security Monitoring)
-- Inclut le User Agent et la Géolocalisation
CREATE TABLE IF NOT EXISTS logs_connexions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adresse_ip VARCHAR(45) NOT NULL,
    pseudo_tente VARCHAR(50),
    statut ENUM('SUCCESS', 'FAILURE') NOT NULL,
    user_agent TEXT,
    location VARCHAR(255),
    date_tentative TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Logs des Actions Administrateur (Logging & Monitoring)
CREATE TABLE IF NOT EXISTS logs_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_admin INT NOT NULL,
    id_cible INT,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    adresse_ip VARCHAR(45) NOT NULL,
    user_agent TEXT,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_admin) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Insertion des utilisateurs de test par défaut
-- Note: Mots de passe à hacher avec Argon2id + Pepper
-- Admin (admin / admin)
-- User  (user / user)
-- Les mots de passe ci-dessous sont des placeholders.
-- Utilisez AuthController pour générer de vrais comptes sécurisés.
INSERT IGNORE INTO utilisateurs (pseudo, email, mot_de_passe, role) VALUES 
('admin', 'admin@example.com', '$argon2id$v=19$m=65536,t=4,p=1$Q0pUeW9PcmZ4b1I3T0ZlZw$place_holder_hash', 'admin'),
('user', 'user@example.com', '$argon2id$v=19$m=65536,t=4,p=1$Q0pUeW9PcmZ4b1I3T0ZlZw$place_holder_hash', 'user');
