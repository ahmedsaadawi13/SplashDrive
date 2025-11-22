<?php
// FILE: /app/core/Session.php

/**
 * Session Class
 * Handles session management and CSRF protection
 */
class Session {
    /**
     * Set session value
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if session key exists
     *
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     *
     * @param string $key
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy session
     */
    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Generate CSRF token
     *
     * @return string
     */
    public static function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        self::set('csrf_token_time', time());
        return $token;
    }

    /**
     * Get CSRF token
     *
     * @return string
     */
    public static function getCsrfToken() {
        if (!self::has('csrf_token')) {
            return self::generateCsrfToken();
        }

        // Check if token is expired
        $tokenTime = self::get('csrf_token_time', 0);
        if (time() - $tokenTime > CSRF_TOKEN_EXPIRE) {
            return self::generateCsrfToken();
        }

        return self::get('csrf_token');
    }

    /**
     * Validate CSRF token
     *
     * @param string $token
     * @return bool
     */
    public static function validateCsrfToken($token) {
        $sessionToken = self::get('csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }

        // Check if token is expired
        $tokenTime = self::get('csrf_token_time', 0);
        if (time() - $tokenTime > CSRF_TOKEN_EXPIRE) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Set flash message
     *
     * @param string $type
     * @param string $message
     */
    public static function setFlash($type, $message) {
        self::set('flash_' . $type, $message);
    }

    /**
     * Get flash message
     *
     * @param string $type
     * @return string|null
     */
    public static function getFlash($type) {
        $message = self::get('flash_' . $type);
        self::remove('flash_' . $type);
        return $message;
    }

    /**
     * Check if flash message exists
     *
     * @param string $type
     * @return bool
     */
    public static function hasFlash($type) {
        return self::has('flash_' . $type);
    }
}
