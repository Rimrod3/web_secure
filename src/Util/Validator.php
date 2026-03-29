<?php

namespace App\Util;

class Validator {
    /**
     * Valide une chaîne de caractères simple (Pseudo, etc.)
     * Autorise Lettres, Chiffres et Underscore par défaut.
     */
    public static function validatePseudo($data) {
        $data = trim($data);
        // Taille entre 3 et 20 caractères, alphanumérique + underscore
        $pattern = '/^[a-zA-Z0-9_]{3,20}$/';
        if (preg_match($pattern, $data)) {
            return $data;
        }
        return false;
    }

    /**
     * Valide une adresse email en vérifiant son format
     * ET en interrogeant les enregistrements DNS (MX) pour s'assurer
     * que le domaine existe réellement et peut recevoir des mails.
     */
    public static function validateEmail($email) {
        $email = trim($email);
        
        // 1. Validation du format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // 2. Validation fonctionnelle (Vérification de l'existence du domaine)
        $domain = substr(strrchr($email, "@"), 1);
        if ($domain !== false) {
            // Vérifie s'il y a un serveur de messagerie (MX) configuré pour ce domaine
            if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) {
                return $email;
            }
        }
        return false;
    }

    /**
     * Valide un message (Ticket)
     * Supprime les balises HTML et limite la taille.
     */
    public static function validateMessage($data, $max = 1000) {
        $data = trim($data);
        if (empty($data) || strlen($data) > $max) {
            return false;
        }
        // Échappement basique pour éviter les problèmes d'affichage ultérieurs
        // (Même si on utilise htmlspecialchars à l'affichage, c'est une sécurité en plus)
        return strip_tags($data);
    }

    /**
     * Valide un ID numérique (pour les suppressions, etc.)
     */
    public static function validateId($data) {
        $id = filter_var($data, FILTER_VALIDATE_INT);
        if ($id !== false && $id > 0) {
            return $id;
        }
        return false;
    }

    /**
     * Valide le type d'action pour la certification
     */
    public static function validateActionType($data) {
        $valid = ['certify', 'decertify'];
        if (in_array($data, $valid)) {
            return $data;
        }
        return false;
    }
}
