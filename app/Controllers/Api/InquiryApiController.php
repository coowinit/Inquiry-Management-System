<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\ApiRequestLog;
use App\Services\InquiryReceiveService;

final class InquiryApiController extends Controller
{
    public function options(): void
    {
        $this->sendCorsHeaders();
        http_response_code(204);
        exit;
    }

    public function submit(): void
    {
        $this->sendCorsHeaders();

        $payload = request_data();

        $authorization = request_header('Authorization');
        if (!isset($payload['api_token']) && is_string($authorization) && starts_with_ignore_case($authorization, 'Bearer ')) {
            $payload['api_token'] = trim(substr($authorization, 7));
        }

        if (!isset($payload['api_token'])) {
            $siteToken = request_header('X-Site-Token');
            if (is_string($siteToken) && $siteToken !== '') {
                $payload['api_token'] = $siteToken;
            }
        }

        $meta = [
            'request_ip' => request_ip(),
            'origin_host' => request_origin_host(),
            'referer_host' => request_referer_host(),
            'referer_url' => $_SERVER['HTTP_REFERER'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_agent_summary' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            'api_token' => $payload['api_token'] ?? '',
            'raw_body' => request_raw_body(),
            'signature' => request_header('X-Signature'),
            'timestamp' => request_header('X-Timestamp'),
        ];

        $service = new InquiryReceiveService();
        $result = $service->handle($payload, $meta);

        (new ApiRequestLog())->create([
            'site_key' => trim((string) ($payload['site_key'] ?? '')),
            'site_id' => $result['body']['data']['site']['id'] ?? null,
            'endpoint' => request_path(),
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'POST',
            'request_ip' => $meta['request_ip'],
            'origin_host' => $meta['origin_host'],
            'referer_host' => $meta['referer_host'],
            'response_status' => $result['status_code'],
            'result_code' => $result['body']['error']['code'] ?? ($result['body']['data']['status'] ?? 'ok'),
            'result_message' => $result['body']['error']['message'] ?? ($result['body']['message'] ?? ''),
            'request_headers_json' => json_encode([
                'Origin' => request_header('Origin'),
                'Content-Type' => request_header('Content-Type'),
                'X-Signature' => request_header('X-Signature') ? '[provided]' : null,
                'X-Timestamp' => request_header('X-Timestamp'),
                'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'response_json' => json_encode($result['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        json_response($result['body'], $result['status_code']);
    }

    public function health(): void
    {
        $this->sendCorsHeaders();
        json_response([
            'success' => true,
            'message' => 'Inquiry receive API is ready.',
            'version' => app_version(),
            'timestamp' => date('c'),
        ]);
    }

    private function sendCorsHeaders(): void
    {
        if (!(bool) config('app.api.enable_cors', true)) {
            return;
        }

        $origin = request_header('Origin');
        if ($origin) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Site-Token, X-Signature, X-Timestamp');
        header('Access-Control-Max-Age: 86400');
    }
}
