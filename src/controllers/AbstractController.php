<?php

namespace App\Controller;

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use App\Models\UserModel;

abstract class AbstractController
{
    /**
     * Instance du moteur de templates Twig.
     *
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Informations sur l'utilisateur connecté.
     * Peut être un tableau associatif contenant les détails de
     * l'utilisateur ou `false` si l'utilisateur n'est pas connecté.
     *
     * @var array|false
     */
    protected array|false $user;

    /**
     * Constructeur de la classe.
     * Initialise le moteur de templates Twig et les informations sur l'utilisateur.
     * Démarre la session si elle n'est pas déjà démarrée.
     */
    public function __construct()
    {
        //je démarre la session si elle n'esdt pas déja démarrée
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Define the path to the views directory if not already defined
        if (!defined('APP_VIEW_PATH')) {
            define('APP_VIEW_PATH', dirname(__DIR__, 2) . '/view');
        }
        $loader = new FilesystemLoader(APP_VIEW_PATH);
        $this->twig = new Environment(
            $loader,
            [
                'cache' => false,
                'debug' => true,
            ]
        );
        $this->twig->addExtension(new DebugExtension());
        $userModel = new UserModel();
        $this->user = $this->initializeUser($userModel);
        $this->twig->addGlobal('user', $this->user);
    }

    /**
     * Initialise les informations de l'utilisateur à partir de la session.
     * Vérifie également les privilèges d'administration.
     *
     * @param UserModel $userModel Instance de UserModel pour récupérer les informations utilisateur.
     * @return array|false Tableau contenant les détails de l'utilisateur ou `false` si l'utilisateur n'est pas trouvé.
     */
    protected function initializeUser(UserModel $userModel): array|false
    {
        if (isset($_SESSION['user']['id'])) {
            $user = $userModel->getUserById($_SESSION['user']['id']);

            return $user ?: false;
        } else {
            return false;
        }
    }
    /**
     * Vérifie si l'utilisateur a les privilèges nécessaires pour accéder à la page.
     *
     * Cette fonction vérifie si une session utilisateur est active.
     * Si l'utilisateur n'est pas connecté (c'est-à-dire si la variable de session
     * `$_SESSION['user']` n'est pas définie),
     * il est redirigé vers la page de connexion.
     *
     * @return void
     */
    protected function checkUserPrivilege()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }
    }
}
