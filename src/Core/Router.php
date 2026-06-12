<?php
// src/Core/Router.php

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function get(string $pattern, callable|array $handler): void
    {
        $this->routes[] = ['GET', $pattern, $handler];
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->routes[] = ['POST', $pattern, $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method) continue;
            $regex  = $this->patternToRegex($pattern);
            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->call($handler, $params);
                return;
            }
        }

        http_response_code(404);
        render('pages/404');
    }

    private function patternToRegex(string $pattern): string
    {
        $pattern = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $pattern);
        return '#^' . $pattern . '$#u';
    }

    private function call(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, [$params]);
            return;
        }
        [$class, $method] = $handler;
        $obj = new $class();
        $obj->$method($params);
    }
}
