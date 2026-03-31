<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, bool $auth = false): void
    {
        $this->add('GET', $path, $handler, $auth);
    }

    public function post(string $path, array|callable $handler, bool $auth = false): void
    {
        $this->add('POST', $path, $handler, $auth);
    }

    public function options(string $path, array|callable $handler, bool $auth = false): void
    {
        $this->add('OPTIONS', $path, $handler, $auth);
    }

    private function add(string $method, string $path, array|callable $handler, bool $auth): void
    {
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'auth' => $auth,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            http_response_code(404);
            View::render('errors/404', [], 'layouts/guest');
            return;
        }

        if ($route['auth'] && !Auth::check()) {
            redirect('login');
        }

        $handler = $route['handler'];

        if (is_callable($handler)) {
            $handler();
            return;
        }

        [$controllerClass, $methodName] = $handler;
        $controller = new $controllerClass();
        $controller->{$methodName}();
    }
}
