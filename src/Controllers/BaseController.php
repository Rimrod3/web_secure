<?php

namespace App\Controllers;

use App\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BaseController {
    protected function view($view, $data = []) {
        // Automatically check for user data for the header
        $user_header_data = null;
        $jwt = $_COOKIE['jwt_token'] ?? null;
        if ($jwt) {
            try {
                $secret_key = Config::getSecretKey();
                $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
                $user_header_data = $decoded->data;
            } catch (\Exception $e) {
                // Token invalid or expired, ignore for header
            }
        }
        
        $data['user_header_data'] = $user_header_data;
        
        extract($data);
        require __DIR__ . "/../Views/{$view}.php";
    }

    protected function redirect($path) {
        // Supprimer le slash de début s'il existe pour forcer une redirection relative
        $path = ltrim($path, '/');
        if (empty($path)) $path = './';
        header("Location: {$path}");
        exit;
    }

    /**
     * Synchronizer Token Pattern for CSRF Protection (Session-based).
     * The token is stored in the session and sent with the form.
     */
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken() {
        $session_token = $_SESSION['csrf_token'] ?? null;
        $post_token = $_POST['csrf_token'] ?? null;
        
        if ($session_token === null || $post_token === null || !hash_equals($session_token, $post_token)) {
            // Unset the token to force regeneration on the next page load.
            unset($_SESSION['csrf_token']);
            
            $s_debug = ($session_token === null) ? "Null" : "Set";
            $p_debug = ($post_token === null) ? "Null" : "Set";

            header("HTTP/1.1 403 Forbidden");
            die("CSRF validation failed. Please try submitting the form again. (Session: $s_debug, Post: $p_debug)");
        }
        
        // Unset the token after successful verification to ensure it's used only once.
        unset($_SESSION['csrf_token']);
    }
}
