<?php
/**
 * Třída pro autentizaci a správu sessions - WEDOS Version
 */

class Auth {
    
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function login($username, $password) {
        require_once __DIR__ . '/../models/User.php';
        
        $userModel = new User();
        $user = $userModel->findByUsername($username);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_active'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
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
    
    public static function canEdit() {
        return in_array(self::role(), ['admin', 'user_edit']);
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