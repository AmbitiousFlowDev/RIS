<?php
require_once 'models/User.php';

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findByCredentials($username, $password);

        if ($user) {
            self::startSession();
            session_regenerate_id(true);
            $_SESSION['user'] = $user;
            return true;
        }

        return false;
    }
    public static function user()
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }
    public static function check(): bool
    {
        return self::user() !== null;
    }
    public static function logout()
    {
        self::startSession();
        unset($_SESSION['user']);
        session_destroy();
    }
    private static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (class_exists('Security')) {
                Security::configureSession();
            }
            session_start();
        }
    }
}