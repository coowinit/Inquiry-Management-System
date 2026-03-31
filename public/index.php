<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InquiryController;
use App\Controllers\SettingsController;
use App\Controllers\SiteController;
use App\Controllers\ToolsController;
use App\Core\Router;

$router = new Router();

$router->get('/', function (): void {
    if (\App\Core\Auth::check()) {
        redirect('dashboard');
    }
    redirect('login');
});

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout'], true);

$router->get('/dashboard', [DashboardController::class, 'index'], true);
$router->get('/inquiries', [InquiryController::class, 'index'], true);
$router->get('/inquiry', [InquiryController::class, 'show'], true);
$router->get('/sites', [SiteController::class, 'index'], true);
$router->get('/tools/blacklist-ips', [ToolsController::class, 'blacklistIps'], true);
$router->get('/profile', [SettingsController::class, 'profile'], true);
$router->post('/profile', [SettingsController::class, 'updateProfile'], true);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', request_path());
