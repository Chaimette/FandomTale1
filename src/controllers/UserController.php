<?php

namespace App\Controller;

use App\Models\UserModel;
use App\Models\RecaptchaModel;
use App\Service\Upload;


class UserController extends AbstractController
{

    private RecaptchaModel $recaptchaModel;
    public function __contruct()
    {
        parent::__construct();
        $this->user = $this->initializeUser(new UserModel());
        $this->twig->addGlobal('user', $this->user);

        // $this->twig->addGlobal('recaptcha', new RecaptchaModel());
    }

    // ! CREER UN PROFILE CONTROLLER POUR GESTION DU PROFILE
    public function profile()
    {
        // Vérifie les privilèges de l'utilisateur
        $this->checkUserPrivilege();

        // Récupère les informations de l'utilisateur
        $user = $this->user;

        // Affiche la vue du profil
        echo $this->twig->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    public function register()
    {
        $defaultPic = "../public/assets/images/default-pp.png";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            $remoteIp = $_SERVER['REMOTE_ADDR'];
            $recaptchaResult = $this->recaptchaModel->verifyRecaptcha($recaptchaResponse, $remoteIp);

            if (!$recaptchaResult['success']) {
                $error = $recaptchaResult['error'];
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }

            $firstname = $_POST['firstname'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $username = $_POST['username'] ?? '';
            $bio = $_POST['bio'] ?? '';
            $avatarUrl = $_POST['avatar_url'] ?? $defaultPic;
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (isset($_FILES['avatar_url']) && $_FILES['avatar_url']['error'] === UPLOAD_ERR_OK) {
                $upload = new Upload();
                $fileResponse = $upload->uploadFile($_FILES['avatar_url']);

                if (is_string($fileResponse)) {
                    $avatarUrl = $fileResponse;
                } else {
                    $error = "Erreur lors du téléchargement de la photo de profil : " . implode(', ', $fileResponse);
                    return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
                }
            }

            // Validation des données
            if ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }
            if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = "Veuillez remplir tous les champs obligatoires.";
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse e-mail n'est pas valide.";
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }
            if (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères.";
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }
            if (strlen($username) < 3 || strlen($username) > 20) {
                $error = "Le nom d'utilisateur doit contenir entre 3 et 20 caractères.";
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }
            if (strlen($firstname) > 50 || strlen($lastname) > 50) {
                $error = "Le prénom et le nom de famille doivent contenir au maximum 50 caractères.";
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }
            if (strlen($bio) > 255) {
                $error = "La biographie doit contenir au maximum 255 caractères.";
                return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
            }

            // Enregistrement de l'utilisateur
            if ($firstname && $lastname && $email && $password) {
                $userModel = new UserModel();
                // Vérifie si l'utilisateur existe déjà
                if ($userModel->getUserByEmail($email)) {
                    $error = "Un utilisateur avec cette adresse e-mail existe déjà.";
                    return $this->twig->render('Auth/register.html.twig', ['error' => $error]);
                }
                // On hash le mdp
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userModel->createUser($firstname, $lastname, $username, $bio, $avatarUrl, $email, $hashedPassword);
                header('Location: /login');
                exit();
            }
        }
        return $this->twig->render('Auth/register.html.twig');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $userModel = new UserModel();
            $user = $userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                // Authentifier l'utilisateur
                $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username'], 'email' => $user['email'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'bio' => $user['bio'], 'avatar_url' => $user['avatar_url'], 'created_at' => $user['created_at'], 'updated_at' => $user['updated_at']];
                header('Location: /dashboard.html.twig');
                exit();
            } else {
                $error = "Email ou mot de passe invalide.";
                return $this->twig->render('Auth/login.html.twig', ['error' => $error]);
            }
        }
        return $this->twig->render('Auth/login.html.twig');
    }

    public function logout()
    {
        session_start();
        unset($_SESSION['user']); // détruit uniquement la session user, pas le reste des données de session
        header('Location: /');
        exit();
    }

    public function deleteAccount()
    {
        session_start();
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /profile');
        exit();
    }
        if (!isset($_SESSION['user']['id'])) {
            header('Location: /login');
            exit();
        }
        $userModel = new UserModel();
        $userId = $_SESSION['user']['id'];
        // Supprime l'utilisateur
        if ($userModel->deleteUserById($userId)) {
            session_unset();
            session_destroy();
            header('Location: /');
            exit();
        } else {
            $error = "Erreur lors de la suppression du compte.";
            return $this->twig->render('user/profile.html.twig', ['error' => $error]);
        }
        //TODO - ADD CSRF TOKEN CHECK

    }
}
