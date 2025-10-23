<?php

namespace App\Services;

class Router
{
    private $routes = [];

    public function get($uri, $callback)
    {
        $this->routes['GET'][$uri] = $callback;
    }

    public function post($uri, $callback)
    {
        $this->routes['POST'][$uri] = $callback;
    }

    public function put($uri, $callback)
    {
        $this->routes['PUT'][$uri] = $callback;
    }

    public function delete($uri, $callback)
    {
        $this->routes['DELETE'][$uri] = $callback;
    }

    public function dispatch()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];
            $parts = explode('@', $callback);
            $controllerName = 'App\\Controllers\\' . $parts[0];
            $methodName = $parts[1];

            $controller = new $controllerName();
            $controller->$methodName();
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }
}
