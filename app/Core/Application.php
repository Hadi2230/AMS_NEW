<?php
/**
 * Aala Niroo AMS - Core Application Class
 */

namespace App\Core;

use PDO;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Application
{
    private PDO $pdo;
    private MonologLogger $logger;
    private array $config;
    
    public function __construct(PDO $pdo, MonologLogger $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->config = $this->loadConfig();
        
        // Initialize Security
        $this->initializeSecurity();
        
        // Initialize Database Tables
        $this->initializeDatabase();
    }
    
    /**
     * Load application configuration
     */
    private function loadConfig(): array
    {
        return [
            'app_name' => $_ENV['APP_NAME'] ?? 'سامانه مدیریت اعلا نیرو',
            'app_env' => $_ENV['APP_ENV'] ?? 'production',
            'app_debug' => $_ENV['APP_DEBUG'] === 'true',
            'app_url' => $_ENV['APP_URL'] ?? 'http://localhost',
            'app_timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Tehran',
            'app_locale' => $_ENV['APP_LOCALE'] ?? 'fa',
            'upload_max_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880),
            'upload_allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf'),
            'backup_enabled' => $_ENV['BACKUP_ENABLED'] === 'true',
            'backup_retention_days' => (int)($_ENV['BACKUP_RETENTION_DAYS'] ?? 30)
        ];
    }
    
    /**
     * Initialize security settings
     */
    private function initializeSecurity(): void
    {
        // Generate CSRF Token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        if ($this->config['app_env'] === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Initialize database tables
     */
    private function initializeDatabase(): void
    {
        try {
            // Create tables if not exists
            $this->createTables();
            
            // Insert initial data if needed
            $this->insertInitialData();
            
            $this->logger->info('Database initialized successfully');
        } catch (\Exception $e) {
            $this->logger->error('Database initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create database tables
     */
    private function createTables(): void
    {
        $tables = [
            // Asset Types Table
            "CREATE TABLE IF NOT EXISTS asset_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                display_name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci",
            
            // Asset Fields Table
            "CREATE TABLE IF NOT EXISTS asset_fields (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type_id INT,
                field_name VARCHAR(100),
                field_type ENUM('text', 'number', 'date', 'select', 'file'),
                is_required BOOLEAN DEFAULT false,
                options TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (type_id) REFERENCES asset_types(id) ON DELETE CASCADE,
                INDEX idx_type_id (type_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci",
            
            // Assets Table
            "CREATE TABLE IF NOT EXISTS assets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type_id INT NOT NULL,
                serial_number VARCHAR(255) UNIQUE,
                purchase_date DATE,
                status ENUM('فعال', 'غیرفعال', 'در حال تعمیر', 'آماده بهره‌برداری') DEFAULT 'فعال',
                brand VARCHAR(255),
                model VARCHAR(255),
                power_capacity VARCHAR(100),
                engine_type VARCHAR(100),
                consumable_type VARCHAR(100),
                engine_model VARCHAR(255),
                engine_serial VARCHAR(255),
                alternator_model VARCHAR(255),
                alternator_serial VARCHAR(255),
                device_model VARCHAR(255),
                device_serial VARCHAR(255),
                control_panel_model VARCHAR(255),
                breaker_model VARCHAR(255),
                fuel_tank_specs TEXT,
                battery VARCHAR(255),
                battery_charger VARCHAR(255),
                heater VARCHAR(255),
                oil_capacity VARCHAR(255),
                radiator_capacity VARCHAR(255),
                antifreeze VARCHAR(255),
                other_items TEXT,
                workshop_entry_date DATE,
                workshop_exit_date DATE,
                datasheet_link VARCHAR(500),
                engine_manual_link VARCHAR(500),
                alternator_manual_link VARCHAR(500),
                control_panel_manual_link VARCHAR(500),
                description TEXT,
                oil_filter_part VARCHAR(100),
                fuel_filter_part VARCHAR(100),
                water_fuel_filter_part VARCHAR(100),
                air_filter_part VARCHAR(100),
                water_filter_part VARCHAR(100),
                custom_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (type_id) REFERENCES asset_types(id) ON DELETE CASCADE,
                INDEX idx_type_id (type_id),
                INDEX idx_status (status),
                INDEX idx_serial (serial_number),
                INDEX idx_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci",
            
            // Customers Table
            "CREATE TABLE IF NOT EXISTS customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_type ENUM('حقیقی','حقوقی') NOT NULL DEFAULT 'حقیقی',
                full_name VARCHAR(255),
                phone VARCHAR(20),
                company VARCHAR(255),
                responsible_name VARCHAR(255),
                company_phone VARCHAR(20),
                responsible_phone VARCHAR(20),
                address TEXT NOT NULL,
                operator_name VARCHAR(255) NOT NULL,
                operator_phone VARCHAR(20) NOT NULL,
                notes TEXT,
                name VARCHAR(255)
                    GENERATED ALWAYS AS (
                        CASE
                            WHEN customer_type = 'حقوقی' AND COALESCE(company,'') <> '' THEN company
                            ELSE full_name
                        END
                    ) STORED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_customer_type (customer_type),
                INDEX idx_full_name (full_name),
                INDEX idx_company (company),
                INDEX idx_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci",
            
            // Users Table
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                full_name VARCHAR(255),
                role ENUM('ادمین', 'کاربر عادی', 'اپراتور') DEFAULT 'کاربر عادی',
                is_active BOOLEAN DEFAULT true,
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci",
            
            // Asset Assignments Table
            "CREATE TABLE IF NOT EXISTS asset_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                asset_id INT NOT NULL,
                customer_id INT NOT NULL,
                assignment_date DATE,
                notes TEXT,
                assignment_status ENUM('فعال', 'خاتمه یافته', 'موقت') DEFAULT 'فعال',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                INDEX idx_asset_id (asset_id),
                INDEX idx_customer_id (customer_id),
                INDEX idx_assignment_date (assignment_date),
                INDEX idx_status (assignment_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci",
            
            // System Logs Table
            "CREATE TABLE IF NOT EXISTS system_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(100) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci"
        ];
        
        foreach ($tables as $table) {
            $this->pdo->exec($table);
        }
    }
    
    /**
     * Insert initial data
     */
    private function insertInitialData(): void
    {
        // Check if data already exists
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM asset_types");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            // Insert asset types
            $this->pdo->exec("INSERT INTO asset_types (name, display_name) VALUES 
                ('generator', 'ژنراتور'),
                ('power_motor', 'موتور برق'),
                ('consumable', 'اقلام مصرفی')");
            
            // Insert default admin user
            $this->pdo->exec("INSERT INTO users (username, password, full_name, role) VALUES
                ('admin', '" . password_hash('admin', PASSWORD_DEFAULT) . "', 'مدیر سیستم', 'ادمین')");
        }
    }
    
    /**
     * Get PDO connection
     */
    public function getDatabase(): PDO
    {
        return $this->pdo;
    }
    
    /**
     * Get logger instance
     */
    public function getLogger(): MonologLogger
    {
        return $this->logger;
    }
    
    /**
     * Get configuration
     */
    public function getConfig(string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        return $this->config[$key] ?? null;
    }
    
    /**
     * Log system action
     */
    public function logAction(string $action, string $description = '', ?int $userId = null): void
    {
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $this->pdo->prepare("INSERT INTO system_logs (user_id, action, description, ip_address, user_agent) 
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $description, $ipAddress, $userAgent]);
        
        $this->logger->info("Action: $action - $description", [
            'user_id' => $userId,
            'ip_address' => $ipAddress
        ]);
    }
}