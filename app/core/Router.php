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
        
        // Debug log
        error_log("Router: Method=$requestMethod, URI=$requestUri");
        
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
            error_log("Router: After base_path removal: $requestUri");
        }
        
        // Odstranění query stringu
        $requestUri = strtok($requestUri, '?');
        
        // Pokud je prázdná URL, nastavit na /
        if (empty($requestUri) || $requestUri === false) {
            $requestUri = '/';
        }
        
        error_log("Router: Final URI to match: $requestUri");
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = $this->convertToRegex($route['path']);
                
                error_log("Router: Trying pattern: $pattern for route: {$route['path']}");
                
                if (preg_match($pattern, $requestUri, $matches)) {
                    error_log("Router: MATCH! Route: {$route['path']}");
                    array_shift($matches); // Odstranit celý match
                    
                    // Odstranit pojmenované klíče (zůstanou pouze číselné)
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (is_numeric($key)) {
                            $params[] = $value;
                        }
                    }
                    
                    error_log("Router: Calling callback with params: " . print_r($params, true));
                    
                    try {
                        return call_user_func_array($route['callback'], $params);
                    } catch (Exception $e) {
                        error_log("Router: Callback error: " . $e->getMessage());
                        error_log("Router: Stack trace: " . $e->getTraceAsString());
                        throw $e;
                    }
                }
            }
        }
        
        // 404 Not Found
        error_log("Router: No route matched - 404");
        http_response_code(404);
        echo "404 - Stránka nenalezena<br>";
        echo "Hledaná URL: " . htmlspecialchars($requestUri) . "<br>";
        echo "<br><strong>Debug info:</strong><br>";
        echo "Request Method: " . htmlspecialchars($requestMethod) . "<br>";
        echo "Original URI: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "<br>";
        echo "Registered routes:<br>";
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                echo "- " . htmlspecialchars($route['method']) . " " . htmlspecialchars($route['path']) . "<br>";
            }
        }
    }
    
    private function convertToRegex($path) {
        // Konverze :id na regex pattern
        $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}