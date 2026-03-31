<?php

declare(strict_types=1);

$apiEndpoint = 'https://your-domain.com/api/v1/inquiries/submit';

$payload = [
    'site_key' => 'a_main',
    'api_token' => 'token_a_main_2026',
    'form_key' => 'contact_form',
    'name' => 'John Smith',
    'email' => 'john@example.com',
    'title' => 'Request for samples',
    'content' => 'We would like to get more information about your WPC products.',
    'country' => 'Australia',
    'phone' => '+61 400 000 000',
    'from_company' => 'Example Pty Ltd',
    'source_url' => 'https://a.com/contact-us/',
    'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'browser' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
    'submitted_at' => date('c'),
    'extra_data' => [
        'product_interest' => 'Decking',
        'quantity' => '200 sqm',
    ],
];

$ch = curl_init($apiEndpoint);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

header('Content-Type: text/plain; charset=utf-8');
echo "HTTP {$httpCode}\n\n";
if ($curlError !== '') {
    echo "cURL Error: {$curlError}\n";
}
echo $response;
