<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ApiRequestLog;
use App\Models\Inquiry;

final class ReportController extends Controller
{
    public function stats(): void
    {
        if (!Auth::can('reports.view')) {
            flash('error', 'You do not have permission to access reports.');
            redirect('dashboard');
        }

        $days = max(7, min(365, (int) ($_GET['days'] ?? 30)));
        $inquiryModel = new Inquiry();
        $apiLogModel = new ApiRequestLog();

        $this->view('dashboard/reports-stats', [
            'pageTitle' => 'Reports & Analytics',
            'days' => $days,
            'overview' => $inquiryModel->overviewForDays($days),
            'trendRows' => $inquiryModel->dailyTrend(min($days, 30)),
            'siteRows' => $inquiryModel->siteBreakdown($days, 12),
            'formRows' => $inquiryModel->topForms(12, $days),
            'countryRows' => $inquiryModel->countrySummary(10, $days),
            'statusRows' => $inquiryModel->statusBreakdown($days),
            'assigneeRows' => $inquiryModel->assigneeSummary($days, 10),
            'apiStatusRows' => $apiLogModel->statusCounts($days),
        ]);
    }
}
