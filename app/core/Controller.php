<?php
// FILE: /app/core/Controller.php

/**
 * Base Controller Class
 * All controllers extend this class
 */
class Controller {
    /**
     * Load a view
     *
     * @param string $view View name
     * @param array $data Data to pass to view
     * @param string $layout Layout to use
     */
    protected function view($view, $data = [], $layout = 'layouts/main') {
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Load the view file
        $viewFile = VIEW_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View not found: $view");
        }

        // Get view content
        $content = ob_get_clean();

        // Load layout if specified
        if ($layout) {
            $layoutFile = VIEW_PATH . '/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Load a model
     *
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model) {
        $modelFile = APP_PATH . '/models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }
        die("Model not found: $model");
    }

    /**
     * Redirect to URL
     *
     * @param string $url
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Return JSON response
     *
     * @param mixed $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get POST data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Get GET data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Check if request is POST
     *
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Validate CSRF token
     *
     * @return bool
     */
    protected function validateCsrf() {
        $token = $this->post('csrf_token');
        return Session::validateCsrfToken($token);
    }

    /**
     * Set flash message
     *
     * @param string $type
     * @param string $message
     */
    protected function setFlash($type, $message) {
        Session::setFlash($type, $message);
    }
}
