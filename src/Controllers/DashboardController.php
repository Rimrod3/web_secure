<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Log;
use App\Middleware\AuthMiddleware;
use App\Util\Validator;

class DashboardController extends BaseController {

    private $userModel;
    private $ticketModel;
    private $logModel;

    public function __construct() {
        $this->userModel = new User();
        $this->ticketModel = new Ticket();
        $this->logModel = new Log();
    }

    public function index() {
        $user_data = AuthMiddleware::check();
        $csrf_token = $this->generateCsrfToken();
        
        // On récupère le profil complet depuis la DB pour avoir la photo_profil à jour
        $current_user_full = $this->userModel->findById($user_data->id);

        $users = [];
        $tickets = [];
        $logs = [];
        $login_logs = [];

        if ($user_data->role === 'admin') {
            $users = $this->userModel->getAll();
            $tickets = $this->ticketModel->getAllWithUser();
            $logs = $this->logModel->getAdminLogs(5);
            $login_logs = $this->logModel->getLoginLogs(10);
        } else {
            $tickets = $this->ticketModel->getByUserId($user_data->id);
        }

        $this->view('dashboard/home', [
            'user_data' => $user_data,
            'current_user_full' => $current_user_full,
            'users' => $users,
            'tickets' => $tickets,
            'logs' => $logs,
            'login_logs' => $login_logs,
            'success_message' => $_SESSION['success_message'] ?? '',
            'error_message' => $_SESSION['error_message'] ?? '',
            'csrf_token' => $csrf_token
        ]);

        unset($_SESSION['success_message'], $_SESSION['error_message']);
    }

    public function sendTicket() {
        $user_data = AuthMiddleware::check();
        $this->verifyCsrfToken();

        $message = Validator::validateMessage($_POST['ticket_message'] ?? '');
        if ($message) {
            if ($this->ticketModel->create($user_data->id, $message)) {
                $_SESSION['success_message'] = "Votre ticket a été envoyé avec succès.";
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'envoi du ticket.";
            }
        } else {
            $_SESSION['error_message'] = "Le message est invalide ou trop long (max 1000 car.).";
        }
        $this->redirect('/');
    }

    public function deleteUser() {
        $user_data = AuthMiddleware::check();
        if ($user_data->role !== 'admin') $this->redirect('/');

        $id_to_delete = Validator::validateId($_GET['delete_user'] ?? 0);

        if ($id_to_delete && $id_to_delete !== (int)$user_data->id) { 
            if ($this->userModel->delete($id_to_delete)) {
                $_SESSION['success_message'] = "Utilisateur supprimé avec succès.";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la suppression de l'utilisateur.";
            }
        } else {
            $_SESSION['error_message'] = "Action impossible ou ID invalide.";
        }
        $this->redirect('/');
    }

    public function manageCertification() {
        $user_data = AuthMiddleware::check();
        if ($user_data->role !== 'admin') $this->redirect('/');
        $this->verifyCsrfToken();

        $id_cible = Validator::validateId($_POST['id_cible'] ?? 0);
        $admin_password = $_POST['admin_password'] ?? '';
        $action_type = Validator::validateActionType($_POST['action_type'] ?? '');

        if (!$id_cible || !$action_type || empty($admin_password)) {
            $_SESSION['error_message'] = "Données du formulaire invalides.";
            $this->redirect('/');
        }

        $nouvel_etat = ($action_type === 'certify') ? 1 : 0;
        $admin_user = $this->userModel->findById($user_data->id);

        $pepper = $_SERVER['PASSWD_PEPPER'] ?? $_ENV['PASSWD_PEPPER'] ?? '';
        $mdp_avec_pepper = $pepper . $admin_password . $pepper;

        if ($admin_user && password_verify($mdp_avec_pepper, $admin_user['mot_de_passe'])) {
            if ($this->userModel->updateCertification($id_cible, $nouvel_etat)) {
                $action_label = ($nouvel_etat === 1) ? 'CERTIFICATION' : 'DECERTIFICATION';
                $this->logModel->createActionLog($user_data->id, $id_cible, $action_label, 'Action via Gestionnaire Pop-up');

                $_SESSION['success_message'] = "Action réussie : L'utilisateur a été " . ($nouvel_etat === 1 ? "certifié" : "décertifié") . ".";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la mise à jour de la base de données.";
            }
        } else {
            $this->logModel->createActionLog($user_data->id, $id_cible, 'ECHEC_AUTH_ADMIN', 'Mauvais mot de passe dans le gestionnaire');
            $_SESSION['error_message'] = "Mot de passe incorrect. Action refusée.";
        }
        $this->redirect('/');
    }

    public function uploadProfilePicture() {
        $user_data = AuthMiddleware::check();
        $this->verifyCsrfToken();

        if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['photo_profil']['tmp_name'];

            if ($_FILES['photo_profil']['size'] > 10 * 1024 * 1024) {
                $_SESSION['error_message'] = "Le fichier est trop volumineux (max 10Mo).";
            } else {
                $file_info = @getimagesize($tmp_name);
                
                if ($file_info !== false) {
                    $mime = $file_info['mime'];
                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                    if (!in_array($mime, $allowed_mimes)) {
                        $_SESSION['error_message'] = "Seuls les formats JPEG, PNG, GIF et WebP sont autorisés.";
                    } else {
                        $image = match($mime) {
                            'image/jpeg' => @imagecreatefromjpeg($tmp_name),
                            'image/png'  => @imagecreatefrompng($tmp_name),
                            'image/gif'  => @imagecreatefromgif($tmp_name),
                            'image/webp' => @imagecreatefromwebp($tmp_name),
                            default      => false,
                        };

                        if ($image !== false) {
                            $new_filename = bin2hex(random_bytes(16)) . '.jpg';
                            $upload_fs_dir = realpath(__DIR__ . '/../../uploads/profiles/');
                            
                            if (!$upload_fs_dir) {
                                $_SESSION['error_message'] = "Dossier d'upload introuvable.";
                            } else {
                                $destination = $upload_fs_dir . '/' . $new_filename;
                                $width = imagesx($image);
                                $height = imagesy($image);
                                $new_image = imagecreatetruecolor($width, $height);
                                $white = imagecolorallocate($new_image, 255, 255, 255);
                                imagefill($new_image, 0, 0, $white);
                                imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $width, $height);

                                if (imagejpeg($new_image, $destination, 85)) {
                                    $photo_path = '/uploads/profiles/' . $new_filename;
                                    $current_user = $this->userModel->findById($user_data->id);
                                    if (!empty($current_user['photo_profil'])) {
                                        $old_path = $upload_fs_dir . '/' . basename($current_user['photo_profil']);
                                        if (file_exists($old_path)) {
                                            @unlink($old_path);
                                        }
                                    }
                                    if ($this->userModel->updateProfilePicture($user_data->id, $photo_path)) {
                                        $_SESSION['success_message'] = "Photo de profil mise à jour et sécurisée avec succès.";
                                    } else {
                                        $_SESSION['error_message'] = "Erreur lors de la mise à jour en base de données.";
                                    }
                                } else {
                                    $_SESSION['error_message'] = "Erreur lors de la reconstruction de l'image sécurisée.";
                                }
                                imagedestroy($image);
                                imagedestroy($new_image);
                            }
                        } else {
                            $_SESSION['error_message'] = "Le fichier image semble corrompu ou invalide.";
                        }
                    }
                } else {
                    $_SESSION['error_message'] = "Le fichier n'est pas une image valide.";
                }
            }
        } else {
            $_SESSION['error_message'] = "Erreur de téléchargement du fichier.";
        }

        // Handle response: JSON for AJAX, redirect for standard forms
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            $response = [];
            if (isset($_SESSION['success_message'])) {
                $response['success'] = true;
                $response['message'] = $_SESSION['success_message'];
            } else {
                $response['success'] = false;
                $response['message'] = $_SESSION['error_message'];
            }
            unset($_SESSION['success_message'], $_SESSION['error_message']);
            echo json_encode($response);
            exit;
        } else {
            $this->redirect('/');
        }
    }
}
