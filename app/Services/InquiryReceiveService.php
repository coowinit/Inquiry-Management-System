<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlacklistIp;
use App\Models\Inquiry;
use App\Models\InquiryLog;
use App\Models\Site;
use DateTime;
use Throwable;

final class InquiryReceiveService
{
    private const STANDARD_FIELDS = [
        'site_key', 'api_token', 'form_key',
        'name', 'email', 'title', 'content', 'country', 'phone', 'address', 'from_company',
        'source_url', 'referer_url', 'language', 'browser', 'device_type', 'submitted_at',
        'client_ip', 'origin', 'extra_data', 'raw_payload', '_csrf', '_token',
    ];

    private Site $siteModel;
    private Inquiry $inquiryModel;
    private BlacklistIp $blacklistIpModel;
    private InquiryLog $logModel;

    public function __construct()
    {
        $this->siteModel = new Site();
        $this->inquiryModel = new Inquiry();
        $this->blacklistIpModel = new BlacklistIp();
        $this->logModel = new InquiryLog();
    }

    public function handle(array $payload, array $serverMeta = []): array
    {
        $siteKey = trim((string) ($payload['site_key'] ?? ''));
        $apiToken = trim((string) ($payload['api_token'] ?? $serverMeta['api_token'] ?? ''));

        if ($siteKey === '' || $apiToken === '') {
            return $this->error('AUTH_REQUIRED', 'site_key and api_token are required.', 422);
        }

        $site = $this->siteModel->findByCredentials($siteKey, $apiToken);
        if (!$site) {
            return $this->error('AUTH_INVALID', 'Invalid site credentials or inactive site.', 401);
        }

        $originHost = $serverMeta['origin_host'] ?? null;
        $refererHost = $serverMeta['referer_host'] ?? null;
        if (!$this->siteModel->isAllowedHost($site, $originHost) || !$this->siteModel->isAllowedHost($site, $refererHost)) {
            return $this->error('ORIGIN_FORBIDDEN', 'Origin or referer host does not match the configured site domain.', 403);
        }

        if ((int) ($site['require_signature'] ?? 0) === 1) {
            $signatureCheck = $this->validateSignature($site, $serverMeta);
            if ($signatureCheck !== true) {
                return $signatureCheck;
            }
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $content = trim((string) ($payload['content'] ?? ''));

        if ($name === '' || $email === '' || $content === '') {
            return $this->error('VALIDATION_FAILED', 'name, email and content are required.', 422, [
                'fields' => [
                    'name' => $name !== '',
                    'email' => $email !== '',
                    'content' => $content !== '',
                ],
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('EMAIL_INVALID', 'Please submit a valid email address.', 422);
        }

        $clientIp = trim((string) ($payload['client_ip'] ?? $serverMeta['request_ip'] ?? ''));
        if ($clientIp === '' || !filter_var($clientIp, FILTER_VALIDATE_IP)) {
            $clientIp = (string) ($serverMeta['request_ip'] ?? '0.0.0.0');
        }

        if ($this->blacklistIpModel->exists($clientIp)) {
            return $this->error('IP_BLOCKED', 'This IP address is blocked.', 403);
        }

        $reasonBag = [];
        $apiConfig = (array) config('app.api', []);
        $honeypotField = (string) ($apiConfig['honeypot_field'] ?? 'website');

        if (!empty($payload[$honeypotField])) {
            $reasonBag[] = 'honeypot_triggered';
        }

        $linkThreshold = (int) ($apiConfig['spam_link_threshold'] ?? 2);
        $linkCount = preg_match_all('/https?:\/\//i', $content) ?: 0;
        if ($linkCount >= $linkThreshold) {
            $reasonBag[] = 'too_many_links';
        }

        $duplicateWindow = (int) ($apiConfig['duplicate_window_minutes'] ?? 10);
        if ($this->inquiryModel->existsRecentDuplicate($email, $content, $duplicateWindow)) {
            $reasonBag[] = 'duplicate_recent_submission';
        }

        $ipWindow = (int) ($apiConfig['ip_rate_limit_window_minutes'] ?? 10);
        $ipMax = (int) ($apiConfig['ip_rate_limit_max'] ?? 8);
        if ($this->inquiryModel->recentCountByIp($clientIp, $ipWindow) >= $ipMax) {
            $reasonBag[] = 'ip_rate_limit';
        }

        $emailWindow = (int) ($apiConfig['email_rate_limit_window_minutes'] ?? 10);
        $emailMax = (int) ($apiConfig['email_rate_limit_max'] ?? 5);
        if ($this->inquiryModel->recentCountByEmail($email, $emailWindow) >= $emailMax) {
            $reasonBag[] = 'email_rate_limit';
        }

        $extraData = $this->collectExtraData($payload);
        if (!empty($reasonBag)) {
            $extraData['_system_flags'] = $reasonBag;
        }

        $submittedAt = null;
        $submittedInput = trim((string) ($payload['submitted_at'] ?? ''));
        if ($submittedInput !== '') {
            try {
                $submittedAt = (new DateTime($submittedInput))->format('Y-m-d H:i:s');
            } catch (Throwable) {
                $submittedAt = null;
            }
        }

        $browser = trim((string) ($payload['browser'] ?? $serverMeta['user_agent_summary'] ?? ''));
        $deviceType = trim((string) ($payload['device_type'] ?? $this->detectDeviceType((string) ($serverMeta['user_agent'] ?? ''))));
        $language = trim((string) ($payload['language'] ?? $serverMeta['accept_language'] ?? ''));

        $status = empty($reasonBag) ? 'unread' : 'spam';
        $isSpam = empty($reasonBag) ? 0 : 1;

        $record = [
            'site_id' => (int) $site['id'],
            'form_key' => trim((string) ($payload['form_key'] ?? 'general_form')),
            'name' => $name,
            'email' => $email,
            'title' => $this->nullableString($payload['title'] ?? null),
            'content' => $content,
            'country' => $this->nullableString($payload['country'] ?? null),
            'phone' => $this->nullableString($payload['phone'] ?? null),
            'address' => $this->nullableString($payload['address'] ?? null),
            'from_company' => $this->nullableString($payload['from_company'] ?? null),
            'source_url' => $this->nullableString($payload['source_url'] ?? null),
            'referer_url' => $this->nullableString($payload['referer_url'] ?? $serverMeta['referer_url'] ?? null),
            'ip' => $clientIp,
            'user_agent' => $this->nullableString((string) ($serverMeta['user_agent'] ?? '')),
            'browser' => $browser !== '' ? $browser : null,
            'device_type' => $deviceType !== '' ? $deviceType : null,
            'language' => $language !== '' ? $language : null,
            'status' => $status,
            'is_read' => 0,
            'is_spam' => $isSpam,
            'admin_note' => null,
            'extra_data' => json_encode($extraData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'raw_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'submitted_at' => $submittedAt,
        ];

        $inquiryId = $this->inquiryModel->create($record);
        $this->logModel->create($inquiryId, null, 'api_received', empty($reasonBag) ? 'Accepted as unread' : 'Accepted as spam: ' . implode(', ', $reasonBag));

        return [
            'ok' => true,
            'status_code' => 201,
            'body' => [
                'success' => true,
                'message' => empty($reasonBag) ? 'Inquiry received successfully.' : 'Inquiry received and flagged as spam.',
                'data' => [
                    'id' => $inquiryId,
                    'status' => $status,
                    'site' => [
                        'id' => (int) $site['id'],
                        'name' => $site['site_name'],
                        'site_key' => $site['site_key'],
                    ],
                    'flags' => $reasonBag,
                ],
            ],
        ];
    }

    private function validateSignature(array $site, array $serverMeta): array|bool
    {
        $providedSignature = trim((string) ($serverMeta['signature'] ?? ''));
        $timestamp = trim((string) ($serverMeta['timestamp'] ?? ''));
        $secret = trim((string) ($site['signature_secret'] ?? ''));
        $rawBody = (string) ($serverMeta['raw_body'] ?? '');

        if ($providedSignature === '' || $timestamp === '' || $secret === '') {
            return $this->error('SIGNATURE_REQUIRED', 'This site requires X-Signature and X-Timestamp headers.', 401);
        }

        if (!ctype_digit($timestamp)) {
            return $this->error('SIGNATURE_TIMESTAMP_INVALID', 'X-Timestamp must be a unix timestamp in seconds.', 401);
        }

        $tolerance = (int) config('app.api.signature_timestamp_tolerance_seconds', 300);
        if (abs(time() - (int) $timestamp) > $tolerance) {
            return $this->error('SIGNATURE_TIMESTAMP_EXPIRED', 'The request timestamp is outside the allowed time window.', 401);
        }

        $normalizedSignature = strtolower($providedSignature);
        if (str_starts_with($normalizedSignature, 'sha256=')) {
            $normalizedSignature = substr($normalizedSignature, 7);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . "\n" . $rawBody, $secret);

        if (!hash_equals($expectedSignature, $normalizedSignature)) {
            return $this->error('SIGNATURE_INVALID', 'The request signature is invalid.', 401);
        }

        return true;
    }

    private function collectExtraData(array $payload): array
    {
        $extraData = [];

        if (isset($payload['extra_data']) && is_array($payload['extra_data'])) {
            $extraData = $payload['extra_data'];
        }

        foreach ($payload as $key => $value) {
            if (in_array((string) $key, self::STANDARD_FIELDS, true)) {
                continue;
            }
            $extraData[$key] = $value;
        }

        return $extraData;
    }

    private function detectDeviceType(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if ($ua === '') {
            return '';
        }

        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'iphone') || str_contains($ua, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function error(string $code, string $message, int $statusCode, array $extra = []): array
    {
        return [
            'ok' => false,
            'status_code' => $statusCode,
            'body' => array_merge([
                'success' => false,
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
            ], $extra),
        ];
    }
}
