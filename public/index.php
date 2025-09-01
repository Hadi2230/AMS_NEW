<?php
// Front controller for the modernized layer (non-breaking: original index.php still exists at root)

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;

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
    $stmt = pdo()->query("SELECT a.*, at.display_name AS type_display_name FROM assets a JOIN asset_types at ON a.type_id = at.id ORDER BY a.created_at DESC LIMIT 200");
    $assets = $stmt->fetchAll();
    App\Core\View::render('assets/index', ['assets' => $assets]);
});

$router->dispatch();

