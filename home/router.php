<?php
// Router script for PHP built-in server
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove query parameters for routing
$route = trim($path, '/');

// Define routes
switch ($route) {
    case '':
    case 'index.php':
        include __DIR__ . '/index.php';
        return true;
    
    case 'aviso-privacidad':
        include __DIR__ . '/privacy-policy.php';
        return true;
    
    case 'condiciones-uso':
        include __DIR__ . '/terms-conditions.php';
        return true;
    
    case 'politicas-devolucion':
        include __DIR__ . '/return-policy.php';
        return true;
    
    case 'uso-cookies':
        include __DIR__ . '/cookies-policy.php';
        return true;
    
    default:
        // For static files, let the server handle them
        if (file_exists(__DIR__ . $path)) {
            return false;
        }
        
        // Otherwise serve index.php (for SPA routing if needed)
        include __DIR__ . '/index.php';
        return true;
}
?> 