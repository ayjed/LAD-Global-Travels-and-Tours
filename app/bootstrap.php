<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — APPLICATION BOOTSTRAP
 * Native PHP 8.2+ initialization without third-party frameworks.
 */

// Define application base paths
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', ROOT_PATH . '/storage');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

// 1. PSR-4 Compliant Autoloader for 'App\' namespace with Linux case-insensitivity support
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);

    // 1. Try standard PSR-4 path (e.g. Core/Env.php)
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // 2. Linux case fallback: lowercased directory names (e.g. core/Env.php, controllers/AdminController.php)
    $parts = explode('\\', $relativeClass);
    if (count($parts) > 1) {
        $className = array_pop($parts);
        $lowerDir = implode('/', array_map('strtolower', $parts));
        $fallback = $baseDir . $lowerDir . '/' . $className . '.php';
        if (file_exists($fallback)) {
            require_once $fallback;
            return;
        }
    }

    // 3. Fully lowercased path fallback (e.g. core/env.php)
    $lowerAll = $baseDir . strtolower(str_replace('\\', '/', $relativeClass)) . '.php';
    if (file_exists($lowerAll)) {
        require_once $lowerAll;
        return;
    }
});

use App\Core\Env;
use App\Core\Security;
use App\Core\View;

// Ensure logs directory exists
if (!is_dir(STORAGE_PATH . '/logs')) {
    @mkdir(STORAGE_PATH . '/logs', 0755, true);
}

// 2. Load Environment Variables from project root (outside public webroot)
Env::load(ROOT_PATH . '/.env');

// 3. Error and Exception Handling Configuration
$isDebug = (bool)Env::get('APP_DEBUG', false);
error_reporting($isDebug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/app.log');

// Global Shutdown Handler to trap Fatal Errors, Parse Errors, and uncatchable exceptions
register_shutdown_function(function () use ($isDebug) {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $logMessage = sprintf(
            "[%s] PHP Fatal Error: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        error_log($logMessage);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($isDebug) {
            echo "<pre style='background:#fce8e6; color:#c5221f; padding:20px; border:2px solid #c5221f; border-radius:8px; font-family:monospace; margin:20px;'>";
            echo "<h3>PHP Fatal Error (Debug Mode Active)</h3>";
            echo "<strong>Message:</strong> " . htmlspecialchars($error['message'], ENT_QUOTES) . "<br>";
            echo "<strong>File:</strong> " . htmlspecialchars($error['file'], ENT_QUOTES) . ":" . $error['line'] . "<br>";
            echo "</pre>";
        } else {
            echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>500 Server Error</title></head><body style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif; text-align:center; padding:60px 20px; background:#F8FAFC; color:#0F172A;'>";
            echo "<div style='max-width:500px; margin:0 auto; background:#FFFFFF; padding:40px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>";
            echo "<h1 style='color:#092A60; margin-top:0;'>500 — Server Error</h1>";
            echo "<p style='color:#64748B; line-height:1.6;'>We are experiencing a temporary technical difficulty. Please refresh the page or check back shortly.</p>";
            echo "<a href='/' style='display:inline-block; margin-top:16px; background:#092A60; color:#FFFFFF; padding:10px 24px; border-radius:6px; text-decoration:none; font-weight:600;'>Return to Homepage</a>";
            echo "</div></body></html>";
        }
    }
});

// Global Exception Handler
set_exception_handler(function (\Throwable $exception) use ($isDebug) {
    $logMessage = sprintf(
        "[%s] Uncaught %s: %s in %s:%d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    error_log($logMessage);

    if (!headers_sent()) {
        http_response_code(500);
    }

    if ($isDebug) {
        echo "<pre style='background:#fce8e6; color:#c5221f; padding:20px; border:2px solid #c5221f; border-radius:8px; font-family:monospace; margin:20px;'>";
        echo "<h3>Application Exception (Debug Mode Enabled)</h3>";
        echo "<strong>Message:</strong> " . htmlspecialchars($exception->getMessage(), ENT_QUOTES) . "<br>";
        echo "<strong>File:</strong> " . htmlspecialchars($exception->getFile(), ENT_QUOTES) . ":" . $exception->getLine() . "<br><br>";
        echo "<strong>Stack Trace:</strong>\n" . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES);
        echo "</pre>";
    } else {
        try {
            View::render('pages/500', [], 'main');
        } catch (\Throwable $e) {
            echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>500 Server Error</title></head><body style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif; text-align:center; padding:60px 20px; background:#F8FAFC; color:#0F172A;'>";
            echo "<div style='max-width:500px; margin:0 auto; background:#FFFFFF; padding:40px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>";
            echo "<h1 style='color:#092A60; margin-top:0;'>500 — Server Error</h1>";
            echo "<p style='color:#64748B; line-height:1.6;'>We are experiencing a temporary technical difficulty. Please refresh the page or check back shortly.</p>";
            echo "<a href='/' style='display:inline-block; margin-top:16px; background:#092A60; color:#FFFFFF; padding:10px 24px; border-radius:6px; text-decoration:none; font-weight:600;'>Return to Homepage</a>";
            echo "</div></body></html>";
        }
    }
});

// 4. Initialize Core Systems
Security::startSecureSession((int)Env::get('SESSION_LIFETIME', 7200));
View::init(APP_PATH . '/views');
