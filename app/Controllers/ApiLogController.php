<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ApiRequestLog;

final class ApiLogController extends Controller
{
    public function index(): void
    {
        if (!Auth::can('api_logs.view')) {
            flash('error', 'You do not have permission to access API request logs.');
            redirect('dashboard');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(20, min(100, (int) (Auth::user()['page_size'] ?? 20)));

        $this->view('dashboard/api-request-logs', [
            'pageTitle' => 'API Request Logs',
            'pagination' => (new ApiRequestLog())->paginate($page, $perPage),
        ]);
    }
}
