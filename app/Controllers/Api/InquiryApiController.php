<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
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

        $service = new InquiryReceiveService();
        $result = $service->handle($payload, [
            'request_ip' => request_ip(),
            'origin_host' => request_origin_host(),
            'referer_host' => request_referer_host(),
            'referer_url' => $_SERVER['HTTP_REFERER'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_agent_summary' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            'api_token' => $payload['api_token'] ?? '',
        ]);

        json_response($result['body'], $result['status_code']);
    }

    public function health(): void
    {
        $this->sendCorsHeaders();
        json_response([
            'success' => true,
            'message' => 'Inquiry receive API is ready.',
            'version' => (string) config('app.api.version', 'v1'),
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
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Site-Token');
        header('Access-Control-Max-Age: 86400');
    }
}
