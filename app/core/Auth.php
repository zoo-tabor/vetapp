<?php
/**
 * Třída pro autentizaci a správu sessions - WEDOS Version
 */

class Auth {
    
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Harden the session cookie. HTTPS is enforced by .htaccess so 'secure'
            // is safe; SameSite=Lax still allows top-level navigation (e.g. /zootrack).
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }
    
    public static function login($username, $password) {
        require_once __DIR__ . '/../models/User.php';
        
        $userModel = new User();
        $user = $userModel->findByUsername($username);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_active'] == 1) {
                // Prevent session fixation: issue a fresh session id on login.
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                // Global ZooTrack edit capability (column added by migration 013).
                $_SESSION['zootrack_edit'] = (int)($user['zootrack_edit'] ?? 0);

                return true;
            }
        }
        
        return false;
    }
    
    public static function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
    
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function username() {
        return $_SESSION['username'] ?? null;
    }
    
    public static function role() {
        return $_SESSION['role'] ?? null;
    }
    
    public static function fullName() {
        return $_SESSION['full_name'] ?? 'Uživatel';
    }
    
    public static function isAdmin() {
        return self::role() === 'admin';
    }
    
    public static function requireLogin() {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }
    
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            die('Přístup odepřen - vyžadována role Admin');
        }
    }
}