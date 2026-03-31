<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Site;

final class SiteController extends Controller
{
    public function index(): void
    {
        $siteModel = new Site();

        $this->view('dashboard/sites', [
            'pageTitle' => 'Sites',
            'sites' => $siteModel->all(),
            'apiEndpoint' => base_url('api/v1/inquiries/submit'),
        ]);
    }
}
