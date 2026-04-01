<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';

use App\Controllers\Api\InquiryApiController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InquiryController;
use App\Controllers\LogController;
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
$router->post('/inquiry/status', [InquiryController::class, 'updateStatus'], true);
$router->post('/inquiry/note', [InquiryController::class, 'updateNote'], true);
$router->get('/inquiries/export', [InquiryController::class, 'exportCsv'], true);

$router->get('/sites', [SiteController::class, 'index'], true);
$router->post('/sites/create', [SiteController::class, 'create'], true);
$router->get('/sites/edit', [SiteController::class, 'edit'], true);
$router->post('/sites/update', [SiteController::class, 'update'], true);
$router->post('/sites/rotate-token', [SiteController::class, 'rotateToken'], true);
$router->post('/sites/rotate-signature-secret', [SiteController::class, 'rotateSignatureSecret'], true);

$router->get('/logs', [LogController::class, 'index'], true);

$router->get('/tools/blacklist-ips', [ToolsController::class, 'blacklistIps'], true);
$router->post('/tools/blacklist-ips', [ToolsController::class, 'addBlacklistIp'], true);
$router->post('/tools/blacklist-ips/delete', [ToolsController::class, 'deleteBlacklistIp'], true);
$router->get('/tools/spam-rules', [ToolsController::class, 'spamRules'], true);
$router->post('/tools/spam-rules', [ToolsController::class, 'updateSpamRules'], true);

$router->get('/profile', [SettingsController::class, 'profile'], true);
$router->post('/profile', [SettingsController::class, 'updateProfile'], true);

$router->options('/api/v1/inquiries/submit', [InquiryApiController::class, 'options']);
$router->post('/api/v1/inquiries/submit', [InquiryApiController::class, 'submit']);
$router->get('/api/v1/health', [InquiryApiController::class, 'health']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', request_path());
