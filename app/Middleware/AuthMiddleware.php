<?php
/**
 * Aala Niroo AMS - Authentication Middleware
 */

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Redirect to login page
            header('Location: /login');
            exit();
        }
        
        // Check if user is active
        if (isset($_SESSION['user_id'])) {
            $pdo = $GLOBALS['pdo'];
            $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || !$user['is_active']) {
                // Log out inactive user
                session_destroy();
                header('Location: /login?error=account_disabled');
                exit();
            }
        }
    }
}