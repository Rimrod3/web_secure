<?php

namespace App\Controllers;

use App\Config;
use App\Models\User;
use App\Util\Validator;
use App\Util\Logger;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthController extends BaseController {
    
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        
        if (session_status() === PHP_SESSION_NONE) {
            $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'cookie_path' => '/', 'cookie_secure' => $is_secure]);
        }
    }

    /**
     * Displays the main authentication page (which contains both login and register forms).
     */
    public function loginForm() {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
            return;
        }
        $csrf_token = $this->generateCsrfToken();
        $this->view('auth/login', ['csrf_token' => $csrf_token]);
    }

    /**
     * Handles the AJAX login request.
     */
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->verifyCsrfToken($data['csrf_token'] ?? '');

        $pseudo = Validator::validatePseudo($data['pseudo'] ?? '');
        $mdp = $data['mdp'] ?? '';

        if (!$pseudo || empty($mdp)) {
            $this->jsonError('Invalid username or password.', 401);
        }

        $user = $this->userModel->findByPseudo($pseudo);
        
        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            Logger::info('User login successful.', ['pseudo' => $pseudo]);
            $this->createJwtSession($user);
            $this->jsonSuccess(['redirect' => '/']);
        } else {
            Logger::warning('User login failed: Invalid credentials.', ['pseudo' => $pseudo]);
            $this->jsonError('Invalid username or password.', 401);
        }
    }

    /**
     * Handles the AJAX registration request.
     */
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->verifyCsrfToken($data['csrf_token'] ?? '');
        
        $pseudo = Validator::validatePseudo($data['pseudo'] ?? '');
        $email = Validator::validateEmail($data['email'] ?? '');
        $mdp = $data['mdp'] ?? '';

        if (!$pseudo || !$email || empty($mdp)) {
            $this->jsonError('Please fill in all fields correctly.', 400);
        }

        if ($this->userModel->findByPseudo($pseudo)) {
            $this->jsonError('This username is already taken.', 409);
        }

        if ($this->userModel->findByEmail($email)) {
            $this->jsonError('This email address is already in use.', 409);
        }

        $passwordSecurity = $this->verifierSecuriteMotDePasse($mdp, $pseudo);
        if (!$passwordSecurity['valid']) {
            $this->jsonError($passwordSecurity['message'], 400);
        }

        $mdp_hache = password_hash($mdp, PASSWORD_ARGON2ID);

        try {
            $this->userModel->create($pseudo, $email, null, $mdp_hache);
            Logger::info('New user registered successfully.', ['pseudo' => $pseudo, 'email' => $email]);
            $this->jsonSuccess(['message' => 'Registration successful! You can now log in.']);
        } catch (\PDOException $e) {
            Logger::error('Database error during user creation.', ['error' => $e->getMessage()]);
            $this->jsonError('A database error occurred.', 500);
        }
    }

    public function logout() {
        setcookie("jwt_token", "", time() - 3600, "/");
        $this->redirect('/login');
    }

    private function isLoggedIn(): bool {
        $jwt = $_COOKIE['jwt_token'] ?? null;
        if (!$jwt) return false;
        try {
            JWT::decode($jwt, new Key(Config::getSecretKey(), 'HS256'));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function createJwtSession(array $user): void {
        $secret_key = Config::getSecretKey();
        $issued_at = time();
        $expiration_time = $issued_at + 3600; // 1 hour

        $token_payload = [
            "iss" => "your_app_name", "aud" => "your_app_name", "iat" => $issued_at, "exp" => $expiration_time,
            "data" => ["id" => $user['id'], "pseudo" => $user['pseudo'], "role" => $user['role']]
        ];

        $jwt = JWT::encode($token_payload, $secret_key, 'HS256');
        $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        
        setcookie("jwt_token", $jwt, ['expires' => $expiration_time, 'path' => '/', 'secure' => $is_secure, 'httponly' => true, 'samesite' => 'Strict']);
    }

    private function verifierSecuriteMotDePasse(string $password, string $username): array {
        if (strlen($password) < 12) return ['valid' => false, 'message' => "Password must be at least 12 characters long."];
        if (!empty($username) && stripos($password, $username) !== false) return ['valid' => false, 'message' => "Password cannot contain your username."];
        if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)) return ['valid' => false, 'message' => "Password must include uppercase and lowercase letters."];
        if (!preg_match('/\d/', $password)) return ['valid' => false, 'message' => "Password must include at least one number."];
        if (!preg_match('/[@$!%*?&]/', $password)) return ['valid' => false, 'message' => "Password must include at least one special symbol (@$!%*?&)."];
        return ['valid' => true, 'message' => "OK"];
    }
}
