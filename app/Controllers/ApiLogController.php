<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ApiRequestLog;
use App\Models\Site;

final class ApiLogController extends Controller
{
    public function index(): void
    {
        if (!Auth::can('api_logs.view')) {
            flash('error', 'You do not have permission to access API request logs.');
            redirect('dashboard');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(20, min(100, (int) (Auth::user()['page_size'] ?? 20)));
        $filters = [
            'site_id' => trim((string) ($_GET['site_id'] ?? '')),
            'status_class' => trim((string) ($_GET['status_class'] ?? '')),
            'keyword' => trim((string) ($_GET['keyword'] ?? '')),
        ];

        $this->view('dashboard/api-request-logs', [
            'pageTitle' => 'API Request Logs',
            'pagination' => (new ApiRequestLog())->paginate($page, $perPage, $filters),
            'filters' => $filters,
            'sites' => (new Site())->all(),
        ]);
    }

    public function show(): void
    {
        if (!Auth::can('api_logs.view')) {
            flash('error', 'You do not have permission to access API request logs.');
            redirect('dashboard');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $log = (new ApiRequestLog())->findById($id);
        if (!$log) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'Not Found'], 'layouts/guest');
            return;
        }

        $this->view('dashboard/api-request-log-detail', [
            'pageTitle' => 'API Log Detail',
            'log' => $log,
            'headersJson' => $this->prettyJson($log['request_headers_json'] ?? ''),
            'payloadJson' => $this->prettyJson($log['payload_json'] ?? ''),
            'responseJson' => $this->prettyJson($log['response_json'] ?? ''),
        ]);
    }

    private function prettyJson(string $value): string
    {
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return $value;
        }
        return (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
