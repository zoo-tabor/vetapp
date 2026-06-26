<?php
/**
 * Jednoduchý router pro zpracování URL - WEDOS Version s debug logem
 */

class Router {
    private $routes = [];
    
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }
    
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }
    
    private function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Načíst config
        $configFile = __DIR__ . '/../config/config.php';
        if (!file_exists($configFile)) {
            die("Config file not found: $configFile");
        }

        $config = require $configFile;
        $basePath = $config['base_path'];

        // Odstranění base path z URL
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        // Odstranění query stringu
        $requestUri = strtok($requestUri, '?');

        // Pokud je prázdná URL, nastavit na /
        if (empty($requestUri) || $requestUri === false) {
            $requestUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = $this->convertToRegex($route['path']);

                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches); // Odstranit celý match

                    // Odstranit pojmenované klíče (zůstanou pouze číselné)
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (is_numeric($key)) {
                            $params[] = $value;
                        }
                    }

                    try {
                        return call_user_func_array($route['callback'], $params);
                    } catch (Exception $e) {
                        // Genuine errors only; the global handler logs the full trace.
                        error_log("Router callback error on {$route['path']}: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
    }
    
    private function convertToRegex($path) {
        // Konverze :id na regex pattern
        $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}