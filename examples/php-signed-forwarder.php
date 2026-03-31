<?php

declare(strict_types=1);

/**
 * Example: signed backend forwarder for a site with require_signature enabled.
 */

$endpoint = 'https://your-central-domain.com/api/v1/inquiries/submit';
$siteKey = 'b_sample';
$apiToken = 'token_b_sample_2026';
$signatureSecret = 'sig_b_sample_2026_secret_1234567890';

$payload = [
    'site_key' => $siteKey,
    'api_token' => $apiToken,
    'form_key' => 'sample_form',
    'name' => 'John Smith',
    'email' => 'john@example.com',
    'title' => 'Request for free samples',
    'content' => 'Please send us your decking sample options.',
    'country' => 'United States',
    'from_company' => 'Acme Inc.',
    'source_url' => 'https://b.com/free-samples/',
    'extra_data' => [
        'product_interest' => 'Decking',
        'project_stage' => 'Planning',
    ],
];

$rawBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$timestamp = (string) time();
$signature = hash_hmac('sha256', $timestamp . "\n" . $rawBody, $signatureSecret);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Timestamp: ' . $timestamp,
        'X-Signature: ' . $signature,
    ],
    CURLOPT_POSTFIELDS => $rawBody,
]);

$response = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . PHP_EOL;
if ($error) {
    echo "cURL Error: " . $error . PHP_EOL;
}
echo "Response: " . $response . PHP_EOL;
