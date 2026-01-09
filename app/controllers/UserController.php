<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/User.php';

class UserController {

    public function settings() {
        Auth::requireLogin();

        $userModel = new User();
        $user = $userModel->findById(Auth::userId());

        View::render('user/settings', [
            'layout' => 'main',
            'title' => 'Moje nastavení',
            'user' => $user
        ]);
    }

    public function updateSettings() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/user/settings');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';

        $userModel = new User();
        $user = $userModel->findById(Auth::userId());

        $errors = [];

        // Validate full name
        if (empty($fullName)) {
            $errors[] = 'Celé jméno je povinné';
        }

        // If changing password, validate
        if (!empty($newPassword) || !empty($currentPassword)) {
            if (empty($currentPassword)) {
                $errors[] = 'Zadejte současné heslo';
            } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                $errors[] = 'Současné heslo je nesprávné';
            }

            if (empty($newPassword)) {
                $errors[] = 'Zadejte nové heslo';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'Nové heslo musí mít minimálně 6 znaků';
            } elseif ($newPassword !== $newPasswordConfirm) {
                $errors[] = 'Nová hesla se neshodují';
            }
        }

        if (!empty($errors)) {
            View::render('user/settings', [
                'layout' => 'main',
                'title' => 'Moje nastavení',
                'user' => $user,
                'errors' => $errors,
                'old_full_name' => $fullName
            ]);
            return;
        }

        // Update user
        try {
            if (!empty($newPassword)) {
                $userModel->updateUser(
                    Auth::userId(),
                    $user['username'],
                    $newPassword,
                    $fullName,
                    $user['email'],
                    $user['role'],
                    $user['is_active']
                );
            } else {
                $userModel->updateUser(
                    Auth::userId(),
                    $user['username'],
                    null,
                    $fullName,
                    $user['email'],
                    $user['role'],
                    $user['is_active']
                );
            }

            View::render('user/settings', [
                'layout' => 'main',
                'title' => 'Moje nastavení',
                'user' => $userModel->findById(Auth::userId()),
                'success' => 'Nastavení bylo úspěšně uloženo'
            ]);

        } catch (Exception $e) {
            View::render('user/settings', [
                'layout' => 'main',
                'title' => 'Moje nastavení',
                'user' => $user,
                'errors' => ['Chyba při ukládání: ' . $e->getMessage()],
                'old_full_name' => $fullName
            ]);
        }
    }
}
