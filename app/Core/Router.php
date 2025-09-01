<?php
/**
 * Aala Niroo AMS - Router Class
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    
    public function __construct()
    {
        $this->loadRoutes();
    }
    
    /**
     * Load application routes
     */
    private function loadRoutes(): void
    {
        // Authentication routes
        $this->addRoute('GET', '/login', 'AuthController@showLogin');
        $this->addRoute('POST', '/login', 'AuthController@login');
        $this->addRoute('GET', '/logout', 'AuthController@logout');
        
        // Dashboard
        $this->addRoute('GET', '/dashboard', 'DashboardController@index', ['auth']);
        
        // Assets management
        $this->addRoute('GET', '/assets', 'AssetController@index', ['auth']);
        $this->addRoute('GET', '/assets/create', 'AssetController@create', ['auth']);
        $this->addRoute('POST', '/assets', 'AssetController@store', ['auth']);
        $this->addRoute('GET', '/assets/{id}', 'AssetController@show', ['auth']);
        $this->addRoute('GET', '/assets/{id}/edit', 'AssetController@edit', ['auth']);
        $this->addRoute('PUT', '/assets/{id}', 'AssetController@update', ['auth']);
        $this->addRoute('DELETE', '/assets/{id}', 'AssetController@destroy', ['auth']);
        
        // Customers management
        $this->addRoute('GET', '/customers', 'CustomerController@index', ['auth']);
        $this->addRoute('GET', '/customers/create', 'CustomerController@create', ['auth']);
        $this->addRoute('POST', '/customers', 'CustomerController@store', ['auth']);
        $this->addRoute('GET', '/customers/{id}', 'CustomerController@show', ['auth']);
        $this->addRoute('GET', '/customers/{id}/edit', 'CustomerController@edit', ['auth']);
        $this->addRoute('PUT', '/customers/{id}', 'CustomerController@update', ['auth']);
        $this->addRoute('DELETE', '/customers/{id}', 'CustomerController@destroy', ['auth']);
        
        // Assignments management
        $this->addRoute('GET', '/assignments', 'AssignmentController@index', ['auth']);
        $this->addRoute('GET', '/assignments/create', 'AssignmentController@create', ['auth']);
        $this->addRoute('POST', '/assignments', 'AssignmentController@store', ['auth']);
        $this->addRoute('GET', '/assignments/{id}', 'AssignmentController@show', ['auth']);
        $this->addRoute('GET', '/assignments/{id}/edit', 'AssignmentController@edit', ['auth']);
        $this->addRoute('PUT', '/assignments/{id}', 'AssignmentController@update', ['auth']);
        $this->addRoute('DELETE', '/assignments/{id}', 'AssignmentController@destroy', ['auth']);
        
        // Reports
        $this->addRoute('GET', '/reports', 'ReportController@index', ['auth']);
        $this->addRoute('GET', '/reports/assets', 'ReportController@assets', ['auth']);
        $this->addRoute('GET', '/reports/customers', 'ReportController@customers', ['auth']);
        $this->addRoute('GET', '/reports/assignments', 'ReportController@assignments', ['auth']);
        
        // Admin routes
        $this->addRoute('GET', '/admin/users', 'Admin\UserController@index', ['auth', 'admin']);
        $this->addRoute('GET', '/admin/logs', 'Admin\LogController@index', ['auth', 'admin']);
        
        // API routes
        $this->addRoute('GET', '/api/assets', 'Api\AssetController@index', ['auth']);
        $this->addRoute('POST', '/api/assets', 'Api\AssetController@store', ['auth']);
        $this->addRoute('GET', '/api/customers', 'Api\CustomerController@index', ['auth']);
        $this->addRoute('POST', '/api/customers', 'Api\CustomerController@store', ['auth']);
    }
    
    /**
     * Add a route
     */
    private function addRoute(string $method, string $path, string $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    /**
     * Dispatch the request to appropriate controller
     */
    public function dispatch(string $uri, string $method): void
    {
        $route = $this->findRoute($uri, $method);
        
        if (!$route) {
            throw new \Exception("Route not found: $method $uri");
        }
        
        // Run middleware
        $this->runMiddleware($route['middleware']);
        
        // Parse handler
        [$controllerName, $action] = explode('@', $route['handler']);
        
        // Extract parameters from URI
        $params = $this->extractParams($route['path'], $uri);
        
        // Create controller instance
        $controllerClass = "App\\Controllers\\$controllerName";
        $controller = new $controllerClass();
        
        // Call the action
        $controller->$action($params);
    }
    
    /**
     * Find matching route
     */
    private function findRoute(string $uri, string $method): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Match path pattern with URI
     */
    private function matchPath(string $pattern, string $uri): bool
    {
        // Convert pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $uri);
    }
    
    /**
     * Extract parameters from URI
     */
    private function extractParams(string $pattern, string $uri): array
    {
        $params = [];
        
        // Find parameter names
        preg_match_all('/\{([^}]+)\}/', $pattern, $paramNames);
        
        // Convert pattern to regex for extraction
        $regex = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        // Extract values
        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches); // Remove full match
            
            foreach ($paramNames[1] as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
        }
        
        return $params;
    }
    
    /**
     * Run middleware
     */
    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $middlewareName) {
            $middlewareClass = "App\\Middleware\\" . ucfirst($middlewareName) . "Middleware";
            $middlewareInstance = new $middlewareClass();
            $middlewareInstance->handle();
        }
    }
}