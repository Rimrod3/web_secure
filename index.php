<?php
ob_start();

/**
 * Le projet utilise désormais JWT pour l'authentification (Stateless).
 * La session PHP n'est conservée QUE pour les messages flash (success/error).
 * La protection CSRF est passée en "Double Submit Cookie" (BaseController).
 */
$is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_path'     => '/',
    'cookie_secure'   => $is_secure
]);

// Réparation des données POST si elles sont vides
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        parse_str($input, $_POST);
    }
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Config;
use App\Router;

// --- DÉBOGAGE : ACTIVER TOUTES LES ERREURS ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

Config::loadEnv();
Config::setSecurityHeaders();

$router = new Router();

// Routes
$router->add('GET', '/', [App\Controllers\DashboardController::class, 'index']);
$router->add('GET', '/professor', [App\Controllers\PageController::class, 'professor']);
$router->add('GET', '/localization', [App\Controllers\PageController::class, 'localization']);
$router->add('GET', '/about', [App\Controllers\PageController::class, 'about']);
$router->add('GET', '/login', [App\Controllers\AuthController::class, 'loginForm']);
$router->add('POST', '/login', [App\Controllers\AuthController::class, 'login']);
$router->add('POST', '/register', [App\Controllers\AuthController::class, 'register']);
$router->add('GET', '/logout', [App\Controllers\AuthController::class, 'logout']);
$router->add('GET', '/dev-unban', [App\Controllers\AuthController::class, 'devUnban']);

// Dashboard actions
$router->add('POST', '/ticket', [App\Controllers\DashboardController::class, 'sendTicket']);
$router->add('GET', '/delete-user', [App\Controllers\DashboardController::class, 'deleteUser']);
$router->add('POST', '/certify', [App\Controllers\DashboardController::class, 'manageCertification']);
$router->add('POST', '/upload-profile-picture', [App\Controllers\DashboardController::class, 'uploadProfilePicture']);

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Nettoyage de l'URI - Version dynamique et robuste
$base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_path = ($base_dir === '/') ? '/' : $base_dir . '/';

if (strpos($uri, $base_path) === 0) {
    // Enlève le base path pour ne garder que la route relative
    $uri = substr($uri, strlen($base_path) - 1);
}

// Supprimer la query string
$uri = explode('?', $uri)[0];

// Toujours s'assurer d'avoir un "/" au début
if (empty($uri)) $uri = '/';
if ($uri[0] !== '/') $uri = '/' . $uri;

define('BASE_URL', $base_path);

$router->dispatch($method, $uri);
