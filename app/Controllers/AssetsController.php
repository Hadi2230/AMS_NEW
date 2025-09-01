<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use function pdo;

final class AssetsController extends Controller
{
    public function index(): void
    {
        $stmt = pdo()->query("SELECT a.*, at.display_name AS type_display_name FROM assets a JOIN asset_types at ON a.type_id = at.id ORDER BY a.created_at DESC LIMIT 200");
        $assets = $stmt->fetchAll();
        $this->view('assets/index', ['assets' => $assets]);
    }
}

