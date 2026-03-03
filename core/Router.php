<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function post(string $path, callable|array $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        $method = strtoupper($method);
        $handlers = $this->routes[$method] ?? [];
        foreach ($handlers as $path => $handler) {
            $pattern = '#^' . preg_quote($path, '#') . '$#';
            if ($path === $uri || preg_match($pattern, $uri)) {
                if (is_array($handler) && count($handler) === 2) {
                    [$class, $method] = $handler;
                    (new $class())->$method();
                } else {
                    $handler();
                }
                return;
            }
        }
        http_response_code(404);
        echo '404 Not Found';
    }
}
