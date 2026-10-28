<?php
/**
 * Utility Helpers
 */

declare(strict_types=1);

namespace Core;

class Helpers
{
    /**
     * Escape string untuk mencegah XSS
     */
    public static function e(?string $string): string
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format angka desimal untuk representasi saintifik
     */
    public static function formatNumber(float|int|string $val, int $decimals = 4): string
    {
        return number_format((float)$val, $decimals, '.', '');
    }

    /**
     * Flash messages
     */
    public static function setFlash(string $type, string $message): void
    {
        Auth::init();
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash(): ?array
    {
        Auth::init();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Redirect helper
     */
    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
