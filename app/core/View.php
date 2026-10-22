<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Server-rendered View Engine and Layout Manager.
 * Renders template files with automatic XSS escaping helpers and layout slots.
 */
class View
{
    private static string $viewsPath;

    public static function init(string $viewsPath): void
    {
        self::$viewsPath = rtrim($viewsPath, '/\\');
    }

    /**
     * Render a view file inside an optional layout.
     *
     * @param string $view Relative view name (e.g. 'pages/home' or 'pages/404')
     * @param array $data Variables to pass to the view
     * @param string|null $layout Layout file name (e.g. 'main', 'admin', or null for no layout)
     * @return void
     */
    public static function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: {$viewFile}", 500);
        }

        // Extract variables for view scope
        extract($data, EXTR_SKIP);

        // Handy security escaping helper alias
        $e = fn(?string $val) => Security::e($val);
        $csrfField = Security::csrfField();
        $csrfToken = Security::csrfToken();

        // Capture view content buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If no layout requested, output view buffer directly
        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = self::$viewsPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Layout file not found: {$layoutFile}", 500);
        }

        // Render layout wrapping the content
        require $layoutFile;
    }

    /**
     * Render a view file to string rather than directly echoing.
     */
    public static function renderToString(string $view, array $data = [], ?string $layout = 'main'): string
    {
        ob_start();
        self::render($view, $data, $layout);
        return (string)ob_get_clean();
    }
}
