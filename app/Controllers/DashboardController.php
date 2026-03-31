<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Inquiry;


final class DashboardController extends Controller
{
    public function index(): void
    {
        $inquiryModel = new Inquiry();

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'user' => Auth::user(),
            'stats' => $inquiryModel->stats(),
            'latestInquiries' => $inquiryModel->latest(6),
        ]);
    }
}
