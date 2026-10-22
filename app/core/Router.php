<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight, zero-dependency HTTP Router.
 * Supports GET/POST methods, static paths, dynamic parameterized segments ({param}),
 * custom 404/405 handlers, and closure/controller dispatches.
 */
class Router
{
    private array $routes = [];
    private $notFoundHandler = null;
    private $methodNotAllowedHandler = null;

    /**
     * Register a GET route.
     *
     * @param string $path
     * @param callable|array $handler
     * @return self
     */
    public function get(string $path, callable|array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route.
     *
     * @param string $path
     * @param callable|array $handler
     * @return self
     */
    public function post(string $path, callable|array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add a route with method, path, and handler.
     */
    public function addRoute(string $method, string $path, callable|array $handler): self
    {
        $normalizedPath = $this->normalizePath($path);
        $pattern = $this->compileRoutePattern($normalizedPath);

        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $normalizedPath,
            'pattern' => $pattern,
            'handler' => $handler
        ];

        return $this;
    }

    /**
     * Set a custom 404 Not Found handler.
     */
    public function setNotFoundHandler(callable|array $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Normalize incoming URI paths.
     */
    private function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }
        return '/' . trim($path, '/');
    }

    /**
     * Convert `{parameter}` placeholders into named regex capture groups.
     */
    private function compileRoutePattern(string $path): string
    {
        if ($path === '/') {
            return '#^/$#';
        }

        // Replace {param} with named capture group (?P<param>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Test whether a route matches without executing its handler.
     *
     * @param string $requestMethod
     * @param string $requestUri
     * @return array|null Returns ['handler' => ..., 'params' => ...] if matched, null otherwise
     */
    public function match(string $requestMethod, string $requestUri): ?array
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $path = $this->normalizePath($path);
        $requestMethod = strtoupper($requestMethod);

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, function ($key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_KEY);

                return [
                    'handler' => $route['handler'],
                    'params'  => $params,
                    'path'    => $route['path']
                ];
            }
        }

        return null;
    }

    /**
     * Match and dispatch an incoming HTTP request.
     *
     * @param string $requestMethod
     * @param string $requestUri
     * @return mixed
     */
    public function dispatch(string $requestMethod, string $requestUri): mixed
    {
        // Strip query string
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $path = $this->normalizePath($path);
        $requestMethod = strtoupper($requestMethod);

        $matchedMethod = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                if ($route['method'] === $requestMethod) {
                    // Extract named parameters only
                    $params = array_filter($matches, function ($key) {
                        return is_string($key);
                    }, ARRAY_FILTER_USE_KEY);

                    return $this->executeHandler($route['handler'], $params);
                }
                $matchedMethod = true;
            }
        }

        // 405 Method Not Allowed
        if ($matchedMethod) {
            http_response_code(405);
            if ($this->methodNotAllowedHandler) {
                return call_user_func($this->methodNotAllowedHandler);
            }
            echo "405 Method Not Allowed";
            return null;
        }

        // 404 Not Found
        http_response_code(404);
        if ($this->notFoundHandler) {
            return call_user_func($this->notFoundHandler);
        }

        echo "404 Page Not Found";
        return null;
    }

    /**
     * Execute a route handler (Closure or [ControllerClass, 'method']).
     */
    private function executeHandler(callable|array $handler, array $params = []): mixed
    {
        if (is_callable($handler)) {
            return call_user_func($handler, $params);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            if (is_string($class) && class_exists($class)) {
                $instance = new $class();
                if (method_exists($instance, $method)) {
                    return $instance->$method($params);
                }
            }
        }

        throw new \RuntimeException("Invalid route handler configured.", 500);
    }

    /**
     * Retrieve all registered routes (for debugging/testing).
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
