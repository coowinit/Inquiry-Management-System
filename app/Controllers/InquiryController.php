<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Inquiry;
use App\Models\InquiryLog;
use App\Models\Site;

final class InquiryController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $user = Auth::user();
        $perPage = max(10, min(100, (int) ($user['page_size'] ?? 20)));

        $filters = [
            'status' => trim((string) ($_GET['status'] ?? '')),
            'site_id' => (int) ($_GET['site_id'] ?? 0),
            'keyword' => trim((string) ($_GET['keyword'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];

        if ($filters['site_id'] === 0) {
            $filters['site_id'] = null;
        }

        $inquiryModel = new Inquiry();
        $siteModel = new Site();
        $pagination = $inquiryModel->paginate($filters, $page, $perPage);

        $this->view('dashboard/inquiries', [
            'pageTitle' => 'Inquiry Management',
            'pagination' => $pagination,
            'filters' => $filters,
            'sites' => $siteModel->all(),
            'csrfToken' => Csrf::token(),
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

        if ($inquiry['status'] === 'unread') {
            $inquiryModel->updateStatus($id, 'read');
            (new InquiryLog())->create($id, Auth::id(), 'viewed', 'Marked as read from detail page');
            $inquiry = $inquiryModel->find($id);
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
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function updateStatus(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('inquiries');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        $allowed = ['unread', 'read', 'trash', 'spam'];

        if ($id <= 0 || !in_array($status, $allowed, true)) {
            flash('error', 'Invalid inquiry status request.');
            redirect('inquiries');
        }

        $inquiryModel = new Inquiry();
        $updated = $inquiryModel->updateStatus($id, $status);

        if ($updated) {
            (new InquiryLog())->create($id, Auth::id(), 'status_changed', 'Changed status to ' . $status);
            flash('success', 'Inquiry status updated to ' . ucfirst($status) . '.');
        } else {
            flash('error', 'Unable to update inquiry status.');
        }

        $back = trim((string) ($_POST['back'] ?? 'inquiries'));
        redirect($back);
    }
}
