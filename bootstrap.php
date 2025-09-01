<?php
/**
 * Aala Niroo AMS - Bootstrap File
 * 
 * این فایل نقطه شروع برنامه است و تمام تنظیمات اولیه را انجام می‌دهد
 */

// Load Composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load Environment Variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set Error Reporting
if ($_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set Timezone
date_default_timezone_set($_ENV['APP_TIMEZONE']);

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize Application
use App\Core\Application;
use App\Core\Database;
use App\Core\Logger;

try {
    // Initialize Database
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Initialize Logger
    $logger = new Logger();
    
    // Initialize Application
    $app = new Application($pdo, $logger);
    
    // Set Global Variables
    $GLOBALS['app'] = $app;
    $GLOBALS['pdo'] = $pdo;
    $GLOBALS['logger'] = $logger;
    
} catch (Exception $e) {
    // Log Error
    error_log("Application Bootstrap Error: " . $e->getMessage());
    
    // Show Error Page
    http_response_code(500);
    include __DIR__ . '/resources/views/errors/500.php';
    exit();
}