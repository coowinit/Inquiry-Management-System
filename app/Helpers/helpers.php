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

    return $cache[$file][$item] ?? $default;
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
