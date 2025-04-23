<?php
// app/Core/Router.php

namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, callable|array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path'   => $this->convertPath($path),
            'handler' => $handler,
            'original' => $path
        ];
    }

    public function dispatch(string $method, string $uri): void {
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($method !== $route['method']) continue;

            if (preg_match($route['path'], $uri, $matches)) {
                array_shift($matches); // Supprimer le match complet

                if (is_array($route['handler'])) {
                    [$controller, $action] = $route['handler'];
                    (new $controller)->$action(...$matches);
                } else {
                    call_user_func_array($route['handler'], $matches);
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
    }

    private function convertPath(string $path): string {
        $regex = preg_replace('#\{([\w]+)\}#', '([^/]+)', $path);
        return '#^' . rtrim($regex, '/') . '$#';
    }
}
