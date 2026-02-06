<?php
/**
 * Authentication Controller
 * 
 * This controller handles login, registration, and authentication.
 * Currently, the logic is embedded in the view files (as per original structure).
 */

// Note: CONFIG_PATH is now defined in bootstrap.php which is loaded by index.php

require_once CONTROLLERS_PATH . '/BaseController.php';
require_once MODELS_PATH . '/User.php';

class AuthController extends BaseController {
    public function __construct() {
        // ensure config is loaded by bootstrap
    }

    public function index() {
        $this->render('auth/index');
    }

    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = User::findByEmail($email);
            if ($user) {
                $stored = $user['password'];
                $ok = false;
                // support hashed passwords and plain-text fallback
                if (password_verify($password, $stored)) {
                    $ok = true;
                } elseif ($password === $stored) {
                    $ok = true;
                }

                if ($ok) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nom'] = $user['nom'] ?? '';
                    $_SESSION['prenom'] = $user['prenom'] ?? '';

                    switch ($user['role']) {
                        case 'admin':
                            $target = PUBLIC_URL . '/index.php/admindash';
                            header('X-Debug-Redirect: ' . $target);
                            $this->redirect($target);
                            break;
                        case 'professor':
                            $target = PUBLIC_URL . '/index.php/profdash';
                            header('X-Debug-Redirect: ' . $target);
                            $this->redirect($target);
                            break;
                        default:
                            $target = PUBLIC_URL . '/index.php/studdash';
                            header('X-Debug-Redirect: ' . $target);
                            $this->redirect($target);
                    }
                } else {
                    $error = 'Invalid credentials. Please verify and try again.';
                }
            } else {
                $error = 'Account not found. Please check your email.';
            }
        }

        $this->render('auth/login', ['error' => $error]);
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        $target = PUBLIC_URL . '/index.php/login/login';
        header('X-Debug-Redirect: ' . $target);
        $this->redirect($target);
    }

    public function forgotPassword() { $this->render('auth/forgot_password'); }
    public function requestAccount() { $this->render('auth/requestacc'); }
    public function resetPassword() { $this->render('auth/resetpass'); }
    public function resetpass() { $this->render('auth/resetpass'); } // Alias for URL routing
    public function testMail() { $this->render('auth/testmail'); }
}
?>