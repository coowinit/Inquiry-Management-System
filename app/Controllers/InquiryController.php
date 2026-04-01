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

        $filters = $this->collectFilters();

        $inquiryModel = new Inquiry();
        $siteModel = new Site();
        $pagination = $inquiryModel->paginate($filters, $page, $perPage);
        $allowedExportFields = $inquiryModel->allowedExportColumns();
        $selectedExportFields = $this->collectExportFields(array_keys($allowedExportFields));

        $this->view('dashboard/inquiries', [
            'pageTitle' => 'Inquiry Management',
            'pagination' => $pagination,
            'filters' => $filters,
            'sites' => $siteModel->all(),
            'csrfToken' => Csrf::token(),
            'allowedExportFields' => $allowedExportFields,
            'selectedExportFields' => $selectedExportFields,
        ]);
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $inquiryModel = new Inquiry();
        $logModel = new InquiryLog();
        $inquiry = $inquiryModel->find($id);

        if (!$inquiry) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'Not Found'], 'layouts/guest');
            return;
        }

        if ($inquiry['status'] === 'unread') {
            $inquiryModel->updateStatus($id, 'read');
            $logModel->create($id, Auth::id(), 'viewed', 'Marked as read from detail page');
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
            'logs' => $logModel->latestForInquiry($id, 8),
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

    public function updateNote(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('inquiries');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $note = trim((string) ($_POST['admin_note'] ?? ''));

        if ($id <= 0) {
            flash('error', 'Invalid inquiry id.');
            redirect('inquiries');
        }

        $updated = (new Inquiry())->updateNote($id, $note !== '' ? $note : null);

        if ($updated) {
            (new InquiryLog())->create($id, Auth::id(), 'note_updated', $note !== '' ? 'Updated admin note' : 'Cleared admin note');
            flash('success', 'Admin note saved successfully.');
        } else {
            flash('error', 'Unable to save admin note.');
        }

        redirect('inquiry?id=' . $id);
    }

    public function exportCsv(): void
    {
        $filters = $this->collectFilters();
        $inquiryModel = new Inquiry();
        $allowedColumns = array_keys($inquiryModel->allowedExportColumns());
        $selectedFields = $this->collectExportFields($allowedColumns);
        $rows = $inquiryModel->exportRows($filters, $selectedFields, 5000);

        (new InquiryLog())->create(null, Auth::id(), 'inquiries_exported', 'Exported ' . count($rows) . ' rows as CSV with fields: ' . implode(', ', $selectedFields));

        send_csv_download(
            'inquiries-' . date('Ymd-His') . '.csv',
            $selectedFields,
            $rows
        );
    }

    private function collectFilters(): array
    {
        $filters = [
            'status' => trim((string) ($_GET['status'] ?? '')),
            'site_id' => (int) ($_GET['site_id'] ?? 0),
            'keyword' => trim((string) ($_GET['keyword'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
            'has_note' => trim((string) ($_GET['has_note'] ?? '')),
        ];

        if ($filters['site_id'] === 0) {
            $filters['site_id'] = null;
        }

        return $filters;
    }

    private function collectExportFields(array $allowed): array
    {
        $fields = $_GET['fields'] ?? [];
        $fields = is_array($fields) ? array_map('strval', $fields) : [];
        $fields = array_values(array_intersect($fields, $allowed));

        return $fields !== [] ? $fields : $allowed;
    }
}
