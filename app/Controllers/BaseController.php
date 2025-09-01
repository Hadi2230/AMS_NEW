<?php
/**
 * Aala Niroo AMS - Base Controller
 */

namespace App\Controllers;

use App\Core\Application;
use App\Core\Database;
use App\Core\Logger;

abstract class BaseController
{
    protected Database $db;
    protected Logger $logger;
    protected Application $app;
    
    public function __construct()
    {
        $this->db = new Database();
        $this->logger = new Logger();
        $this->app = $GLOBALS['app'] ?? null;
    }
    
    /**
     * Render a view
     */
    protected function render(string $view, array $data = []): void
    {
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewPath = __DIR__ . '/../../resources/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $view");
        }
        
        include $viewPath;
    }
    
    /**
     * Redirect to another page
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit();
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    /**
     * Get current user ID
     */
    protected function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    protected function getCurrentUserRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->getCurrentUserRole() === 'ادمین';
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            
            if (!hash_equals($sessionToken, $token)) {
                $this->logger->security('CSRF token validation failed', [
                    'user_id' => $this->getCurrentUserId(),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);
                
                throw new \Exception('درخواست نامعتبر است');
            }
        }
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[$field] = "فیلد $field الزامی است";
            }
        }
        
        return $errors;
    }
    
    /**
     * Log user action
     */
    protected function logAction(string $action, string $description = ''): void
    {
        $this->app->logAction($action, $description, $this->getCurrentUserId());
    }
}