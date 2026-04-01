<?php
// Router script for PHP built-in server
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Serve static files directly (js, css, images, etc.)
$filePath = __DIR__ . $path;
if ($path !== '/' && file_exists($filePath) && is_file($filePath)) {
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'js'   => 'application/javascript',
        'css'  => 'text/css',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'mp4'  => 'video/mp4',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'json' => 'application/json',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile($filePath);
        return true;
    }
    return false;
}

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
        // All other routes serve index.php
        include __DIR__ . '/index.php';
        return true;
}
?> 