<?php

namespace App\Controllers;

use App\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BaseController {

    protected function view($view, $data = []) {
        $user_header_data = null;
        $jwt = $_COOKIE['jwt_token'] ?? null;
        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key(Config::getSecretKey(), 'HS256'));
                $user_header_data = $decoded->data;
            } catch (\Exception $e) {}
        }
        $data['user_header_data'] = $user_header_data;
        extract($data);
        require __DIR__ . "/../Views/{$view}.php";
    }

    protected function redirect($path) {
        $path = ltrim($path, '/');
        if (empty($path)) $path = './';
        header("Location: {$path}");
        exit;
    }

    protected function generateCsrfToken() {
        // Always generate a fresh token on each page load
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifies CSRF token.
     * - AJAX/JSON callers: pass $token explicitly (read from JSON body)
     * - Standard form POST: call with no argument (reads $_POST)
     *
     * Token is NOT consumed for AJAX calls — the user can make multiple
     * requests (e.g. wrong password then retry) without refreshing.
     */
    protected function verifyCsrfToken(?string $token = null) {
        $session_token   = $_SESSION['csrf_token'] ?? null;
        $submitted_token = $token ?? ($_POST['csrf_token'] ?? null);
        $is_ajax         = ($token !== null);

        if (
            $session_token === null
            || $submitted_token === null
            || !hash_equals($session_token, $submitted_token)
        ) {
            if (!$is_ajax) unset($_SESSION['csrf_token']);

            if ($is_ajax) {
                $this->jsonError('Invalid or expired security token. Please refresh the page.', 403);
            }
            http_response_code(403);
            die("CSRF validation failed. Please refresh the page and try again.");
        }

        // Consume token only for traditional form submissions
        if (!$is_ajax) {
            unset($_SESSION['csrf_token']);
        }
    }

    protected function jsonSuccess(array $data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    protected function jsonError(string $message, int $status = 400) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}