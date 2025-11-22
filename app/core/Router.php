<?php
// FILE: /app/core/Router.php

/**
 * Router Class
 * Handles URL routing to controllers and actions
 */
class Router {
    private $routes = [];
    private $params = [];

    /**
     * Add a route
     *
     * @param string $method HTTP method
     * @param string $path URL path
     * @param string $controller Controller name
     * @param string $action Action name
     */
    public function add($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Get route from URL
     *
     * @param string $url
     * @param string $method
     * @return array|false
     */
    public function match($url, $method = 'GET') {
        // Remove query string
        $url = parse_url($url, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            // Convert route path to regex pattern
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $url, $matches)) {
                // Extract named parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $params[$key] = $value;
                    }
                }

                $this->params = $params;

                return [
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'params' => $params
                ];
            }
        }

        return false;
    }

    /**
     * Dispatch the route
     *
     * @param string $url
     * @param string $method
     */
    public function dispatch($url, $method = 'GET') {
        $route = $this->match($url, $method);

        if ($route === false) {
            http_response_code(404);
            echo '404 - Page Not Found';
            return;
        }

        $controllerName = $route['controller'];
        $action = $route['action'];
        $this->params = $route['params'];

        // Load controller
        $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            die("Controller not found: $controllerName");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die("Controller class not found: $controllerName");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            die("Action not found: $action in $controllerName");
        }

        // Call controller action with params
        call_user_func_array([$controller, $action], $this->params);
    }

    /**
     * Get route parameters
     *
     * @return array
     */
    public function getParams() {
        return $this->params;
    }
}
