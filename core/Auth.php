<?php
/**
 * Authentication & Session Management
 * Sistem Pendukung Keputusan MOORA
 */

declare(strict_types=1);

namespace Core;

class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_start();
            } else {
                @session_start();
            }
        }
    }

    public static function check(): bool
    {
        self::init();
        return isset($_SESSION['user_id']) && !empty($_SESSION['username']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'       => $_SESSION['user_id'] ?? null,
            'nama'     => $_SESSION['nama'] ?? '',
            'username' => $_SESSION['username'] ?? '',
            'role'     => $_SESSION['role'] ?? 'user',
        ];
    }

    public static function role(): string
    {
        self::init();
        return $_SESSION['role'] ?? 'user';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function requireAdmin(string $redirectTo = 'dashboard.php'): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            Helpers::setFlash('danger', 'Akses Ditolak: Fitur ini hanya dapat dikelola oleh Administrator (Panitia Seleksi).');
            Helpers::redirect($redirectTo);
        }
    }

    public static function login(string $username, string $password): bool
    {
        self::init();
        $user = Database::fetchOne("SELECT * FROM users WHERE username = :username LIMIT 1", [
            ':username' => $username
        ]);

        if (!$user) {
            return false;
        }

        $isValid = false;

        // Verifikasi password hash standar (BCRYPT)
        if (password_verify($password, $user['password'])) {
            $isValid = true;
        }

        if ($isValid) {
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                session_regenerate_id(true);
            }
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] ?? ($user['username'] === 'admin' ? 'admin' : 'user');
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    public static function requireAuth(string $redirectTo = 'index.php'): void
    {
        if (!self::check()) {
            header("Location: {$redirectTo}");
            exit;
        }
    }
}
