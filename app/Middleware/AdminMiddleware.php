<?php
/**
 * Aala Niroo AMS - Admin Middleware
 */

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        // First check if user is authenticated
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
        
        // Check if user has admin role
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ادمین') {
            // Log unauthorized access attempt
            $GLOBALS['logger']->security('Unauthorized admin access attempt', [
                'user_id' => $_SESSION['user_id'] ?? null,
                'user_role' => $_SESSION['role'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'requested_url' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
            
            // Show access denied page
            http_response_code(403);
            include __DIR__ . '/../../resources/views/errors/403.php';
            exit();
        }
    }
}