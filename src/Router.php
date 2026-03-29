<?php

namespace App;

class Router {
    private $routes = [];

    public function add($method, $path, $callback) {
        $path = preg_replace('/\{([a-z0-9_]+)\}/', '(?P<$1>[a-z0-9_-]+)', $path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '#^' . $path . '$#',
            'callback' => $callback
        ];
    }

    public function dispatch($method, $uri) {
        $method = strtoupper($method);
        
        // Normalize: remove trailing slash except for root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['path'], $uri, $matches)) {
                $callback = $route['callback'];
                if (is_array($callback)) {
                    $controllerName = $callback[0];
                    $methodName = $callback[1];
                    $controller = new $controllerName();
                    return $controller->$methodName($matches);
                }
                return call_user_func($callback, $matches);
            }
        }

        http_response_code(404);
        echo "404 Not Found (URI: $uri, Method: $method)";
    }
}
