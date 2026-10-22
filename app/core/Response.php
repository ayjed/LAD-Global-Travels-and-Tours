<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Response and Flash Messaging Helper.
 */
class Response
{
    private const FLASH_KEY = '_flash_messages';

    /**
     * Set the HTTP response status code.
     */
    public static function status(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Send a redirect response.
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    /**
     * Send a JSON response.
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Store a one-time flash notification message in the session.
     */
    public static function setFlash(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            Security::startSecureSession();
        }

        $_SESSION[self::FLASH_KEY][$type][] = $message;
    }

    /**
     * Alias for setFlash.
     */
    public static function flash(string $type, string $message): void
    {
        self::setFlash($type, $message);
    }

    /**
     * Retrieve and clear all flash messages.
     *
     * @return array Array of messages keyed by type ('success', 'danger', 'info', 'warning')
     */
    public static function getFlashes(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            Security::startSecureSession();
        }

        $flashes = $_SESSION[self::FLASH_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY]);
        return $flashes;
    }
}
