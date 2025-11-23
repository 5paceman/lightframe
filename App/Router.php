<?php

namespace App;

class Router
{
    protected array $routes = [];

    public function get(string $path, callable $handler, array $middleware = [])
    {
        $this->routes['GET'][$path] = compact('handler', 'middleware');
    }

    public function post(string $path, callable $handler, array $middleware = [])
    {
        $this->routes['POST'][$path] = compact('handler', 'middleware');
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes[$method] as $route => $options) {

            // Match routes with simple parameters like /user/{id}
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route);
            $pattern = "#^".$pattern."$#";

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches); // remove full match

                // Run middleware
                foreach ($options['middleware'] as $mw) {
                    $mwResult = $mw($uri);

                    if ($mwResult === false) {
                        return; // stop if middleware blocks
                    }
                }

                return call_user_func_array($options['handler'], $matches);
            }
        }

        http_response_code(404);
        if(Config::error_4xx_view === '')
            echo "404 Not Found";
        else
            Response::view(Config::error_4xx_view);
    }
}
