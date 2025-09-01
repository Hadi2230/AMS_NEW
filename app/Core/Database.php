<?php
/**
 * Aala Niroo AMS - Database Connection Class
 */

namespace App\Core;

use PDO;
use PDOException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Database
{
    private PDO $connection;
    private Logger $logger;
    
    public function __construct()
    {
        $this->logger = new Logger('database');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/database.log'));
        
        $this->connect();
    }
    
    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? 'aala_niroo';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_persian_ci",
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO($dsn, $username, $password, $options);
            
            $this->logger->info('Database connection established successfully');
            
        } catch (PDOException $e) {
            $this->logger->error('Database connection failed: ' . $e->getMessage());
            throw new \Exception('خطا در اتصال به پایگاه داده: ' . $e->getMessage());
        }
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
    
    /**
     * Execute a query and return results
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $this->logger->info('Query executed successfully', [
                'sql' => $sql,
                'params' => $params
            ]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $this->logger->error('Query execution failed: ' . $e->getMessage(), [
                'sql' => $sql,
                'params' => $params
            ]);
            throw new \Exception('خطا در اجرای کوئری: ' . $e->getMessage());
        }
    }
    
    /**
     * Execute a query and return single row
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            
            $this->logger->info('Query executed successfully', [
                'sql' => $sql,
                'params' => $params
            ]);
            
            return $result ?: null;
            
        } catch (PDOException $e) {
            $this->logger->error('Query execution failed: ' . $e->getMessage(), [
                'sql' => $sql,
                'params' => $params
            ]);
            throw new \Exception('خطا در اجرای کوئری: ' . $e->getMessage());
        }
    }
    
    /**
     * Execute a query and return count
     */
    public function count(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            
            $this->logger->info('Count query executed successfully', [
                'sql' => $sql,
                'params' => $params,
                'count' => $result['count'] ?? 0
            ]);
            
            return (int)($result['count'] ?? 0);
            
        } catch (PDOException $e) {
            $this->logger->error('Count query execution failed: ' . $e->getMessage(), [
                'sql' => $sql,
                'params' => $params
            ]);
            throw new \Exception('خطا در اجرای کوئری شمارش: ' . $e->getMessage());
        }
    }
    
    /**
     * Insert data and return last insert ID
     */
    public function insert(string $table, array $data): int
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($data);
            
            $lastInsertId = $this->connection->lastInsertId();
            
            $this->logger->info('Data inserted successfully', [
                'table' => $table,
                'data' => $data,
                'last_insert_id' => $lastInsertId
            ]);
            
            return (int)$lastInsertId;
            
        } catch (PDOException $e) {
            $this->logger->error('Data insertion failed: ' . $e->getMessage(), [
                'table' => $table,
                'data' => $data
            ]);
            throw new \Exception('خطا در درج داده: ' . $e->getMessage());
        }
    }
    
    /**
     * Update data
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        try {
            $setClause = [];
            foreach (array_keys($data) as $column) {
                $setClause[] = "$column = :$column";
            }
            $setClause = implode(', ', $setClause);
            
            $sql = "UPDATE $table SET $setClause WHERE $where";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_merge($data, $whereParams));
            
            $rowCount = $stmt->rowCount();
            
            $this->logger->info('Data updated successfully', [
                'table' => $table,
                'data' => $data,
                'where' => $where,
                'where_params' => $whereParams,
                'affected_rows' => $rowCount
            ]);
            
            return $rowCount;
            
        } catch (PDOException $e) {
            $this->logger->error('Data update failed: ' . $e->getMessage(), [
                'table' => $table,
                'data' => $data,
                'where' => $where,
                'where_params' => $whereParams
            ]);
            throw new \Exception('خطا در به‌روزرسانی داده: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete data
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($whereParams);
            
            $rowCount = $stmt->rowCount();
            
            $this->logger->info('Data deleted successfully', [
                'table' => $table,
                'where' => $where,
                'where_params' => $whereParams,
                'affected_rows' => $rowCount
            ]);
            
            return $rowCount;
            
        } catch (PDOException $e) {
            $this->logger->error('Data deletion failed: ' . $e->getMessage(), [
                'table' => $table,
                'where' => $where,
                'where_params' => $whereParams
            ]);
            throw new \Exception('خطا در حذف داده: ' . $e->getMessage());
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
        $this->logger->info('Transaction begun');
    }
    
    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->connection->commit();
        $this->logger->info('Transaction committed');
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->connection->rollback();
        $this->logger->info('Transaction rolled back');
    }
}