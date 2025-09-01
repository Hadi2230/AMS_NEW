<?php
/**
 * Aala Niroo AMS - Logger Class
 */

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private MonologLogger $logger;
    
    public function __construct()
    {
        $this->logger = new MonologLogger('aala-niroo-ams');
        
        // Create logs directory if not exists
        $logsDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        
        // Main application log
        $appHandler = new RotatingFileHandler(
            $logsDir . '/app.log',
            30, // Keep 30 days
            MonologLogger::INFO
        );
        $appHandler->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        ));
        $this->logger->pushHandler($appHandler);
        
        // Error log
        $errorHandler = new RotatingFileHandler(
            $logsDir . '/error.log',
            30,
            MonologLogger::ERROR
        );
        $errorHandler->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        ));
        $this->logger->pushHandler($errorHandler);
        
        // Security log
        $securityHandler = new StreamHandler(
            $logsDir . '/security.log',
            MonologLogger::INFO
        );
        $securityHandler->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        ));
        $this->logger->pushHandler($securityHandler);
    }
    
    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }
    
    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }
    
    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
    
    /**
     * Log security event
     */
    public function security(string $message, array $context = []): void
    {
        $this->logger->info("SECURITY: $message", $context);
    }
    
    /**
     * Log user action
     */
    public function userAction(int $userId, string $action, string $description = '', array $context = []): void
    {
        $context['user_id'] = $userId;
        $context['action'] = $action;
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $this->logger->info("User Action: $action - $description", $context);
    }
    
    /**
     * Log database operation
     */
    public function database(string $operation, string $table, array $context = []): void
    {
        $context['operation'] = $operation;
        $context['table'] = $table;
        
        $this->logger->info("Database: $operation on $table", $context);
    }
    
    /**
     * Get underlying Monolog logger
     */
    public function getLogger(): MonologLogger
    {
        return $this->logger;
    }
}