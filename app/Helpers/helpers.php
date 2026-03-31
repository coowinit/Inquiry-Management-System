<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    static $cache = [];

    [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

    if (!$file) {
        return $default;
    }

    if (!isset($cache[$file])) {
        $path = __DIR__ . '/../../config/' . $file . '.php';
        $cache[$file] = file_exists($path) ? require $path : [];
    }

    if ($item === null) {
        return $cache[$file] ?? $default;
    }

    $value = $cache[$file];

    foreach (explode('.', $item) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function app_version(): string
{
    static $version = null;

    if (is_string($version)) {
        return $version;
    }

    $path = __DIR__ . '/../../VERSION.txt';
    $version = file_exists($path) ? trim((string) file_get_contents($path)) : 'dev';

    return $version !== '' ? $version : 'dev';
}

function base_url(string $path = ''): string
{
    $base = rtrim((string) config('app.base_url', ''), '/');
    $path = ltrim($path, '/');

    if ($base === '') {
        return '/' . $path;
    }

    return $base . ($path ? '/' . $path : '');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash_old_input(): void
{
    $_SESSION['_old'] = $_POST;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function request_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = rtrim((string) config('app.base_url', ''), '/');

    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }

    return '/' . trim($uri, '/') ?: '/';
}

function request_header(string $name): ?string
{
    $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

    if (isset($_SERVER[$normalized])) {
        return trim((string) $_SERVER[$normalized]);
    }

    if ($name === 'Content-Type' && isset($_SERVER['CONTENT_TYPE'])) {
        return trim((string) $_SERVER['CONTENT_TYPE']);
    }

    if ($name === 'Authorization' && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim((string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }

    return null;
}

function request_raw_body(): string
{
    static $body = null;

    if (is_string($body)) {
        return $body;
    }

    $raw = file_get_contents('php://input');
    $body = is_string($raw) ? $raw : '';

    return $body;
}

function json_input(): array
{
    static $payload = null;

    if (is_array($payload)) {
        return $payload;
    }

    $contentType = strtolower((string) request_header('Content-Type'));

    if (!str_contains($contentType, 'application/json')) {
        $payload = [];
        return $payload;
    }

    $decoded = json_decode(request_raw_body(), true);
    $payload = is_array($decoded) ? $decoded : [];

    return $payload;
}

function request_data(): array
{
    if (is_post()) {
        $json = json_input();
        if (!empty($json)) {
            return $json;
        }
    }

    return $_POST;
}

function json_response(array $data, int $status = 200, array $headers = []): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    foreach ($headers as $name => $value) {
        header($name . ': ' . $value);
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_ip(): string
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
        $_SERVER['HTTP_X_REAL_IP'] ?? null,
        $_SERVER['REMOTE_ADDR'] ?? null,
    ];

    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded !== '') {
        foreach (explode(',', $forwarded) as $ip) {
            $candidates[] = trim($ip);
        }
    }

    foreach ($candidates as $candidate) {
        if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_IP)) {
            return $candidate;
        }
    }

    return '0.0.0.0';
}

function request_origin_host(): ?string
{
    $origin = request_header('Origin');
    if (!$origin) {
        return null;
    }

    $host = parse_url($origin, PHP_URL_HOST);
    return is_string($host) ? strtolower($host) : null;
}

function request_referer_host(): ?string
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer === '') {
        return null;
    }

    $host = parse_url($referer, PHP_URL_HOST);
    return is_string($host) ? strtolower($host) : null;
}

function current_query(array $overrides = []): string
{
    $query = $_GET;

    foreach ($overrides as $key => $value) {
        if ($value === null || $value == '') {
            unset($query[$key]);
            continue;
        }
        $query[$key] = $value;
    }

    return http_build_query($query);
}

function url_with_query(string $path, array $overrides = []): string
{
    $query = current_query($overrides);
    $url = base_url($path);

    return $query !== '' ? $url . '?' . $query : $url;
}

function starts_with_ignore_case(string $haystack, string $needle): bool
{
    return str_starts_with(strtolower($haystack), strtolower($needle));
}

function random_token(int $length = 32): string
{
    $bytes = max(8, intdiv($length + 1, 2));
    return substr(bin2hex(random_bytes($bytes)), 0, $length);
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return '-';
    }

    try {
        return (new DateTime($value))->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return (string) $value;
    }
}

function csv_escape(mixed $value): string
{
    if (is_array($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    return (string) $value;
}

function send_csv_download(string $filename, array $headers, iterable $rows): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'wb');
    fputs($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers);

    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $key) {
            $line[] = csv_escape($row[$key] ?? '');
        }
        fputcsv($output, $line);
    }

    fclose($output);
    exit;
}
