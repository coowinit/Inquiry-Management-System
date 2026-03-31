<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\InquiryLog;

final class LogController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $user = Auth::user();
        $perPage = max(20, min(100, (int) ($user['page_size'] ?? 20)));
        $pagination = (new InquiryLog())->paginate($page, $perPage);

        $this->view('dashboard/logs', [
            'pageTitle' => 'System Logs',
            'pagination' => $pagination,
        ]);
    }
}
