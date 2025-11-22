<?php
// FILE: /config/config.php

/**
 * SplashDrive Configuration File
 * Loads environment variables and sets application constants
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Helper function to get environment variable
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    // Convert string booleans
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
    }

    return $value;
}

// Database configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'splashdrive'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', env('APP_NAME', 'SplashDrive'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_URL', env('APP_URL', 'http://localhost'));
define('APP_TIMEZONE', env('APP_TIMEZONE', 'UTC'));

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Path configuration
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH', APP_PATH . '/views');

// Session configuration
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 7200));
define('CSRF_TOKEN_EXPIRE', env('CSRF_TOKEN_EXPIRE', 3600));

// File upload configuration
define('MAX_UPLOAD_SIZE', env('MAX_UPLOAD_SIZE', 52428800)); // 50MB default
define('ALLOWED_FILE_TYPES', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar'));

// User roles
define('ROLE_PLATFORM_ADMIN', 'platform_admin');
define('ROLE_TENANT_ADMIN', 'tenant_admin');
define('ROLE_USER', 'user');
define('ROLE_READ_ONLY', 'read_only');

// File visibility types
define('VISIBILITY_PRIVATE', 'private');
define('VISIBILITY_TENANT', 'tenant');
define('VISIBILITY_SHARED_LINK', 'shared_link');

// Subscription statuses
define('SUBSCRIPTION_STATUS_ACTIVE', 'active');
define('SUBSCRIPTION_STATUS_INACTIVE', 'inactive');
define('SUBSCRIPTION_STATUS_TRIAL', 'trial');
define('SUBSCRIPTION_STATUS_CANCELLED', 'cancelled');
define('SUBSCRIPTION_STATUS_EXPIRED', 'expired');

// Billing cycles
define('BILLING_CYCLE_MONTHLY', 'monthly');
define('BILLING_CYCLE_YEARLY', 'yearly');

// Invoice statuses
define('INVOICE_STATUS_PENDING', 'pending');
define('INVOICE_STATUS_PAID', 'paid');
define('INVOICE_STATUS_CANCELLED', 'cancelled');
define('INVOICE_STATUS_OVERDUE', 'overdue');

// Payment statuses
define('PAYMENT_STATUS_SUCCESS', 'success');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_PENDING', 'pending');

// Activity log action types
define('ACTION_FILE_UPLOAD', 'file_upload');
define('ACTION_FILE_DOWNLOAD', 'file_download');
define('ACTION_FILE_DELETE', 'file_delete');
define('ACTION_FILE_MOVE', 'file_move');
define('ACTION_FILE_RENAME', 'file_rename');
define('ACTION_FILE_RESTORE', 'file_restore');
define('ACTION_FOLDER_CREATE', 'folder_create');
define('ACTION_FOLDER_DELETE', 'folder_delete');
define('ACTION_FOLDER_MOVE', 'folder_move');
define('ACTION_FOLDER_RENAME', 'folder_rename');
define('ACTION_FOLDER_RESTORE', 'folder_restore');
define('ACTION_SHARE_CREATE', 'share_create');
define('ACTION_SHARE_DELETE', 'share_delete');
define('ACTION_USER_LOGIN', 'user_login');
define('ACTION_USER_LOGOUT', 'user_logout');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Enable sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
