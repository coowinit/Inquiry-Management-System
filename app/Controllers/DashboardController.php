<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Inquiry;
use App\Models\InquiryLog;
use App\Models\Site;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $inquiryModel = new Inquiry();
        $siteModel = new Site();
        $logModel = new InquiryLog();

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'user' => Auth::user(),
            'stats' => $inquiryModel->stats(),
            'latestInquiries' => $inquiryModel->latest(6),
            'sites' => $siteModel->allWithStats(),
            'recentLogs' => $logModel->paginate(1, 6)['data'],
            'apiEndpoint' => base_url('api/v1/inquiries/submit'),
        ]);
    }
}
