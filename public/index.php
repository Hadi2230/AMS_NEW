<?php
// Front controller for the modernized layer (non-breaking: original index.php still exists at root)

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\AssetsController;

$router = new Router();

// Home
$router->get('/', function () {
    require_auth();
    log_action('VIEW_DASHBOARD', 'نمایش داشبورد مدرن');
    header('Location: /index.php');
});

// Assets (read-only list for now)
$router->get('/assets', function () {
    require_auth();
    (new AssetsController())->index();
});

$router->get('/assets/create', function () {
    require_auth();
    (new AssetsController())->create();
});

$router->post('/assets/store', function () {
    require_auth();
    (new AssetsController())->store();
});

// Auth
$router->get('/login', function () {
    (new AuthController())->showLogin();
});
$router->post('/login', function () {
    (new AuthController())->doLogin();
});

$router->dispatch();

