<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inquiry;


final class InquiryController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $inquiryModel = new Inquiry();
        $pagination = $inquiryModel->paginate($page, $perPage);

        $this->view('dashboard/inquiries', [
            'pageTitle' => 'Inquiry Management',
            'pagination' => $pagination,
        ]);
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $inquiryModel = new Inquiry();
        $inquiry = $inquiryModel->find($id);

        if (!$inquiry) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'Not Found'], 'layouts/guest');
            return;
        }

        $extraData = [];
        $rawPayload = [];

        if (!empty($inquiry['extra_data'])) {
            $extraData = json_decode((string) $inquiry['extra_data'], true) ?: [];
        }

        if (!empty($inquiry['raw_payload'])) {
            $rawPayload = json_decode((string) $inquiry['raw_payload'], true) ?: [];
        }

        $this->view('dashboard/inquiry-detail', [
            'pageTitle' => 'Inquiry Detail',
            'inquiry' => $inquiry,
            'extraData' => $extraData,
            'rawPayload' => $rawPayload,
        ]);
    }
}
