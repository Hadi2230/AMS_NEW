<?php
/**
 * Aala Niroo AMS - Public Entry Point
 * 
 * این فایل نقطه ورود اصلی برنامه است
 */

// Load bootstrap
require_once __DIR__ . '/../bootstrap.php';

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string from URI
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// Remove base path if exists
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/') {
    $requestUri = str_replace($basePath, '', $requestUri);
}

// Default to dashboard if no path specified
if ($requestUri === '/' || $requestUri === '') {
    $requestUri = '/dashboard';
}

// Route the request
try {
    $router = new \App\Core\Router();
    $router->dispatch($requestUri, $requestMethod);
} catch (\Exception $e) {
    // Log the error
    $GLOBALS['logger']->error('Routing error: ' . $e->getMessage(), [
        'uri' => $requestUri,
        'method' => $requestMethod
    ]);
    
    // Show error page
    http_response_code(404);
    include __DIR__ . '/../resources/views/errors/404.php';
}