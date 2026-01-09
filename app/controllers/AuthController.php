<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';

class AuthController {
    
    public function showLogin() {
        // Pokud je již přihlášen, přesměrovat na dashboard
        if (Auth::check()) {
            View::redirect('/');
        }
        
        View::render('auth/login', [
            'layout' => false
        ]);
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/login');
            return;
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (Auth::login($username, $password)) {
            View::redirect('/');
        } else {
            View::render('auth/login', [
                'layout' => false,
                'error' => 'Nesprávné přihlašovací údaje'
            ]);
        }
    }
    
    public function logout() {
        Auth::logout();
    }

    public function setupPassword() {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            View::render('auth/setup-password', [
                'layout' => false,
                'error' => 'Chybí token pro nastavení hesla'
            ]);
            return;
        }

        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            View::render('auth/setup-password', [
                'layout' => false,
                'error' => 'Neplatný nebo expirovaný odkaz'
            ]);
            return;
        }

        View::render('auth/setup-password', [
            'layout' => false,
            'token' => $token,
            'username' => $user['username'],
            'full_name' => $user['full_name']
        ]);
    }

    public function processPasswordSetup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/setup-password');
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($token) || empty($password)) {
            View::render('auth/setup-password', [
                'layout' => false,
                'error' => 'Vyplňte všechna pole'
            ]);
            return;
        }

        if ($password !== $passwordConfirm) {
            View::render('auth/setup-password', [
                'layout' => false,
                'token' => $token,
                'error' => 'Hesla se neshodují'
            ]);
            return;
        }

        if (strlen($password) < 6) {
            View::render('auth/setup-password', [
                'layout' => false,
                'token' => $token,
                'error' => 'Heslo musí mít minimálně 6 znaků'
            ]);
            return;
        }

        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();

        if ($userModel->setPasswordFromToken($token, $password)) {
            View::render('auth/setup-password', [
                'layout' => false,
                'success' => 'Heslo bylo úspěšně nastaveno. Nyní se můžete přihlásit.'
            ]);
        } else {
            View::render('auth/setup-password', [
                'layout' => false,
                'token' => $token,
                'error' => 'Neplatný nebo expirovaný odkaz'
            ]);
        }
    }
}