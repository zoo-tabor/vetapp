<?php
/**
 * Třída pro renderování views
 */

class View {
    
    public static function render($viewPath, $data = []) {
        // Extrahovat data do proměnných
        extract($data);
        
        // Načíst view
        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';
        
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            
            // Pokud view obsahuje layout, použít ho
            if (isset($layout) && $layout) {
                $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
                if (file_exists($layoutFile)) {
                    include $layoutFile;
                    return;
                }
            }
            
            echo $content;
        } else {
            die("View not found: " . $viewPath);
        }
    }
    
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
}