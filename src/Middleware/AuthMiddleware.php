<?php

namespace App\Middleware;

use App\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

class AuthMiddleware {
    public static function check() {
        $secret_key = Config::getSecretKey();

        $jwt = $_COOKIE['jwt_token'] ?? null;
        if (!$jwt) {
            $base_path = '/';
            header("Location: {$base_path}/login?error=not_logged_in");
            exit();
        }

        try {
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            return $decoded->data;
        } catch (ExpiredException $e) {
            $base_path = '/';
            header("Location: {$base_path}/login?error=token_expired");
            exit();
        } catch (SignatureInvalidException $e) {
            $base_path = '/';
            header("Location: {$base_path}/login?error=invalid_signature");
            exit();
        } catch (Exception $e) {
            $base_path = '/';
            header("Location: {$base_path}/login?error=invalid_token");
            exit();
        }
    }
}
