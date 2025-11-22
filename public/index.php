<?php
// FILE: /public/index.php

/**
 * SplashDrive Application Entry Point
 * All requests are routed through this file
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Load core classes
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Router.php';
require_once APP_PATH . '/core/Session.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/Validator.php';
require_once APP_PATH . '/core/Uploader.php';

// Initialize router
$router = new Router();

// Authentication routes
$router->add('GET', '/', 'HomeController', 'index');
$router->add('GET', '/auth/login', 'AuthController', 'login');
$router->add('POST', '/auth/login', 'AuthController', 'loginPost');
$router->add('GET', '/auth/register', 'AuthController', 'register');
$router->add('POST', '/auth/register', 'AuthController', 'registerPost');
$router->add('GET', '/auth/logout', 'AuthController', 'logout');

// Dashboard routes
$router->add('GET', '/dashboard', 'DashboardController', 'index');
$router->add('GET', '/dashboard/admin', 'DashboardController', 'admin');

// User management routes
$router->add('GET', '/users', 'UserController', 'index');
$router->add('GET', '/users/create', 'UserController', 'create');
$router->add('POST', '/users/create', 'UserController', 'store');
$router->add('GET', '/users/edit/{id}', 'UserController', 'edit');
$router->add('POST', '/users/edit/{id}', 'UserController', 'update');
$router->add('POST', '/users/delete/{id}', 'UserController', 'delete');

// Tenant management routes
$router->add('GET', '/tenants', 'TenantController', 'index');
$router->add('GET', '/tenants/create', 'TenantController', 'create');
$router->add('POST', '/tenants/create', 'TenantController', 'store');
$router->add('GET', '/tenants/edit/{id}', 'TenantController', 'edit');
$router->add('POST', '/tenants/edit/{id}', 'TenantController', 'update');
$router->add('GET', '/tenants/view/{id}', 'TenantController', 'view');

// File management routes
$router->add('GET', '/files', 'FileController', 'index');
$router->add('GET', '/files/folder/{id}', 'FileController', 'folder');
$router->add('POST', '/files/upload', 'FileController', 'upload');
$router->add('GET', '/files/download/{id}', 'FileController', 'download');
$router->add('POST', '/files/delete/{id}', 'FileController', 'delete');
$router->add('POST', '/files/move/{id}', 'FileController', 'move');
$router->add('POST', '/files/rename/{id}', 'FileController', 'rename');
$router->add('GET', '/files/search', 'FileController', 'search');

// Folder management routes
$router->add('GET', '/folders', 'FolderController', 'index');
$router->add('POST', '/folders/create', 'FolderController', 'create');
$router->add('POST', '/folders/delete/{id}', 'FolderController', 'delete');
$router->add('POST', '/folders/rename/{id}', 'FolderController', 'rename');
$router->add('POST', '/folders/move/{id}', 'FolderController', 'move');

// Trash/Recycle bin routes
$router->add('GET', '/trash', 'TrashController', 'index');
$router->add('POST', '/trash/restore/{type}/{id}', 'TrashController', 'restore');
$router->add('POST', '/trash/delete/{type}/{id}', 'TrashController', 'deletePermanently');
$router->add('POST', '/trash/empty', 'TrashController', 'empty');

// Sharing routes
$router->add('GET', '/share/{token}', 'ShareController', 'view');
$router->add('POST', '/share/create', 'ShareController', 'create');
$router->add('POST', '/share/delete/{id}', 'ShareController', 'delete');
$router->add('GET', '/shares', 'ShareController', 'index');

// Subscription routes
$router->add('GET', '/subscription', 'SubscriptionController', 'index');
$router->add('POST', '/subscription/change', 'SubscriptionController', 'change');
$router->add('GET', '/subscription/plans', 'SubscriptionController', 'plans');

// Billing routes
$router->add('GET', '/billing', 'BillingController', 'index');
$router->add('GET', '/billing/invoices', 'BillingController', 'invoices');
$router->add('GET', '/billing/invoice/{id}', 'BillingController', 'invoice');
$router->add('POST', '/billing/pay/{id}', 'BillingController', 'pay');

// Activity log routes
$router->add('GET', '/activity', 'ActivityController', 'index');
$router->add('GET', '/activity/export', 'ActivityController', 'export');

// API routes
$router->add('GET', '/api/files/list', 'ApiController', 'listFiles');
$router->add('POST', '/api/files/upload', 'ApiController', 'uploadFile');
$router->add('GET', '/api/folders/list', 'ApiController', 'listFolders');

// Get current URL and method
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Dispatch the route
$router->dispatch($url, $method);
