<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ApiRequestLog;
use App\Models\ExportTemplate;
use App\Models\Inquiry;
use App\Models\InquiryFollowup;
use App\Models\InquiryLog;
use App\Models\Site;
use App\Services\EmailNotificationService;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $inquiryModel = new Inquiry();
        $siteModel = new Site();
        $logModel = new InquiryLog();
        $followupModel = new InquiryFollowup();
        $apiLogModel = new ApiRequestLog();
        $userId = Auth::id() ?? 0;

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'user' => Auth::user(),
            'stats' => $inquiryModel->stats(),
            'latestInquiries' => $inquiryModel->latest(6),
            'sites' => $siteModel->allWithStats(),
            'recentLogs' => $logModel->paginate(1, 6)['data'],
            'recentApiLogs' => $apiLogModel->recent(6),
            'apiStatusCounts' => $apiLogModel->statusCounts(30),
            'apiEndpoint' => base_url('api/v1/inquiries/submit'),
            'trendRows' => $inquiryModel->dailyTrend(7),
            'topForms' => $inquiryModel->topForms(6),
            'countrySummary' => $inquiryModel->countrySummary(6),
            'notificationSettings' => (new EmailNotificationService())->getSettings(),
            'openFollowupsCount' => $followupModel->countOpenByAssignee($userId),
            'followupReminderStats' => $followupModel->reminderStats(in_array(Auth::role(), ['admin', 'manager'], true) ? null : $userId),
            'upcomingFollowups' => $followupModel->upcomingForDashboard(in_array(Auth::role(), ['admin', 'manager'], true) ? null : $userId, 6),
            'exportTemplateCount' => count((new ExportTemplate())->allVisibleTo($userId)),
        ]);
    }
}
