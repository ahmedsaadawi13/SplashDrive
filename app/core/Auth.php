<?php
// FILE: /app/core/Auth.php

/**
 * Auth Class
 * Handles authentication and authorization
 */
class Auth {
    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public static function check() {
        return Session::has('user_id');
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    public static function id() {
        return Session::get('user_id');
    }

    /**
     * Get current user data
     *
     * @return array|null
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        $user = Session::get('user_data');
        return $user;
    }

    /**
     * Get current user's tenant ID
     *
     * @return int|null
     */
    public static function tenantId() {
        if (!self::check()) {
            return null;
        }

        return Session::get('tenant_id');
    }

    /**
     * Get current user's role
     *
     * @return string|null
     */
    public static function role() {
        $user = self::user();
        return $user ? $user['role'] : null;
    }

    /**
     * Check if user has role
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole($role) {
        return self::role() === $role;
    }

    /**
     * Check if user is platform admin
     *
     * @return bool
     */
    public static function isPlatformAdmin() {
        return self::hasRole(ROLE_PLATFORM_ADMIN);
    }

    /**
     * Check if user is tenant admin
     *
     * @return bool
     */
    public static function isTenantAdmin() {
        return self::hasRole(ROLE_TENANT_ADMIN);
    }

    /**
     * Check if user is regular user
     *
     * @return bool
     */
    public static function isUser() {
        return self::hasRole(ROLE_USER);
    }

    /**
     * Check if user is read-only
     *
     * @return bool
     */
    public static function isReadOnly() {
        return self::hasRole(ROLE_READ_ONLY);
    }

    /**
     * Check if user can perform action
     *
     * @param string $action
     * @return bool
     */
    public static function can($action) {
        $role = self::role();

        $permissions = [
            ROLE_PLATFORM_ADMIN => ['*'], // All permissions
            ROLE_TENANT_ADMIN => [
                'manage_users',
                'manage_billing',
                'view_analytics',
                'manage_files',
                'manage_folders',
                'share_files',
                'delete_permanently',
                'view_activity_log'
            ],
            ROLE_USER => [
                'manage_files',
                'manage_folders',
                'share_files'
            ],
            ROLE_READ_ONLY => [
                'view_files',
                'download_files'
            ]
        ];

        if (!isset($permissions[$role])) {
            return false;
        }

        // Platform admin has all permissions
        if (in_array('*', $permissions[$role])) {
            return true;
        }

        return in_array($action, $permissions[$role]);
    }

    /**
     * Login user
     *
     * @param array $user
     */
    public static function login($user) {
        Session::set('user_id', $user['id']);
        Session::set('user_data', $user);
        Session::set('tenant_id', $user['tenant_id']);

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Logout user
     */
    public static function logout() {
        Session::destroy();
    }

    /**
     * Require authentication
     */
    public static function require() {
        if (!self::check()) {
            header('Location: /auth/login');
            exit;
        }
    }

    /**
     * Require role
     *
     * @param string|array $roles
     */
    public static function requireRole($roles) {
        self::require();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRole = self::role();
        if (!in_array($userRole, $roles)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    /**
     * Require permission
     *
     * @param string $permission
     */
    public static function requirePermission($permission) {
        self::require();

        if (!self::can($permission)) {
            http_response_code(403);
            die('Access denied');
        }
    }
}
