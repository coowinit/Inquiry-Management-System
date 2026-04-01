<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Admin;
use App\Models\Inquiry;
use App\Models\InquiryFollowup;
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
        $adminModel = new Admin();
        $pagination = $inquiryModel->paginate($filters, $page, $perPage);
        $allowedExportFields = $inquiryModel->allowedExportColumns();
        $selectedExportFields = $this->collectExportFields(array_keys($allowedExportFields));

        $this->view('dashboard/inquiries', [
            'pageTitle' => 'Inquiry Management',
            'pagination' => $pagination,
            'filters' => $filters,
            'sites' => $siteModel->all(),
            'admins' => $adminModel->allBrief(),
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
        $followupModel = new InquiryFollowup();
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
            'followups' => $followupModel->latestForInquiry($id, 20),
            'admins' => (new Admin())->allBrief(),
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

        $updated = (new Inquiry())->updateStatus($id, $status);

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

    public function assign(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('inquiries');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $adminIdRaw = trim((string) ($_POST['assigned_admin_id'] ?? ''));
        $adminId = $adminIdRaw !== '' ? (int) $adminIdRaw : null;

        if ($id <= 0) {
            flash('error', 'Invalid inquiry id.');
            redirect('inquiries');
        }

        $updated = (new Inquiry())->updateAssignedAdmin($id, $adminId);

        if ($updated) {
            $assigneeText = $adminId ? 'Assigned to admin #' . $adminId : 'Cleared assignee';
            (new InquiryLog())->create($id, Auth::id(), 'assignee_updated', $assigneeText);
            flash('success', 'Inquiry assignee updated successfully.');
        } else {
            flash('error', 'Unable to update inquiry assignee.');
        }

        redirect('inquiry?id=' . $id);
    }

    public function addFollowup(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('inquiries');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $type = trim((string) ($_POST['followup_type'] ?? 'note'));
        $content = trim((string) ($_POST['content'] ?? ''));
        $nextContactAt = trim((string) ($_POST['next_contact_at'] ?? ''));
        $isCompleted = isset($_POST['is_completed']) ? 1 : 0;

        if ($id <= 0 || $content === '') {
            flash('error', 'Follow-up content is required.');
            redirect('inquiry?id=' . $id);
        }

        if (!in_array($type, ['note', 'email', 'call', 'meeting', 'todo', 'status'], true)) {
            $type = 'note';
        }

        $nextContactAtValue = null;
        if ($nextContactAt !== '') {
            $timestamp = strtotime($nextContactAt);
            if ($timestamp !== false) {
                $nextContactAtValue = date('Y-m-d H:i:s', $timestamp);
            }
        }

        $followupId = (new InquiryFollowup())->create([
            'inquiry_id' => $id,
            'admin_id' => Auth::id(),
            'followup_type' => $type,
            'content' => $content,
            'next_contact_at' => $nextContactAtValue,
            'is_completed' => $isCompleted,
        ]);

        if ($followupId > 0) {
            (new InquiryLog())->create($id, Auth::id(), 'followup_added', 'Added ' . $type . ' follow-up #' . $followupId);
            flash('success', 'Follow-up record added successfully.');
        } else {
            flash('error', 'Unable to add follow-up record.');
        }

        redirect('inquiry?id=' . $id);
    }

    public function bulkUpdate(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('inquiries');
        }

        $ids = $_POST['ids'] ?? [];
        $ids = is_array($ids) ? $ids : [];
        $action = trim((string) ($_POST['bulk_action'] ?? ''));
        $inquiryModel = new Inquiry();
        $logModel = new InquiryLog();

        if ($ids === []) {
            flash('error', 'Please select at least one inquiry.');
            redirect('inquiries?' . current_query());
        }

        $affected = 0;
        if (in_array($action, ['mark_unread', 'mark_read', 'mark_spam', 'move_trash'], true)) {
            $statusMap = [
                'mark_unread' => 'unread',
                'mark_read' => 'read',
                'mark_spam' => 'spam',
                'move_trash' => 'trash',
            ];
            $status = $statusMap[$action];
            $affected = $inquiryModel->bulkUpdateStatus($ids, $status);
            $logModel->create(null, Auth::id(), 'bulk_status_changed', 'Bulk updated ' . $affected . ' inquiries to ' . $status);
        } elseif (in_array($action, ['assign_selected', 'clear_assignee'], true)) {
            $adminId = $action === 'assign_selected' ? (int) ($_POST['bulk_assigned_admin_id'] ?? 0) : null;
            $affected = $inquiryModel->bulkAssign($ids, $adminId > 0 ? $adminId : null);
            $logModel->create(null, Auth::id(), 'bulk_assignee_updated', 'Bulk updated assignee for ' . $affected . ' inquiries');
        } else {
            flash('error', 'Please choose a valid bulk action.');
            redirect('inquiries?' . current_query());
        }

        flash('success', 'Bulk action completed for ' . $affected . ' inquiries.');
        redirect('inquiries?' . current_query());
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
        $assigned = trim((string) ($_GET['assigned_admin_id'] ?? ''));

        $filters = [
            'status' => trim((string) ($_GET['status'] ?? '')),
            'site_id' => (int) ($_GET['site_id'] ?? 0),
            'keyword' => trim((string) ($_GET['keyword'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
            'has_note' => trim((string) ($_GET['has_note'] ?? '')),
            'assigned_admin_id' => $assigned,
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
