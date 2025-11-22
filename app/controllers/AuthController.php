<?php
// FILE: /app/controllers/AuthController.php

/**
 * AuthController
 * Handles user authentication (login, register, logout)
 */
class AuthController extends Controller {
    private $userModel;
    private $activityModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * Show login form
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('auth/login', [
            'title' => 'Login - ' . APP_NAME
        ]);
    }

    /**
     * Process login
     */
    public function loginPost() {
        if (!$this->isPost()) {
            $this->redirect('/auth/login');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/auth/login');
            return;
        }

        $email = $this->post('email');
        $password = $this->post('password');

        // Validate input
        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->required('email', 'Email is required')
                  ->email('email', 'Invalid email address')
                  ->required('password', 'Password is required');

        if ($validator->fails()) {
            $this->setFlash('error', $validator->getFirstError());
            $this->redirect('/auth/login');
            return;
        }

        // Verify credentials
        $user = $this->userModel->verifyLogin($email, $password);

        if (!$user) {
            $this->setFlash('error', 'Invalid email or password');
            $this->redirect('/auth/login');
            return;
        }

        // Login user
        Auth::login($user);

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $user['tenant_id'],
            'user_id' => $user['id'],
            'action_type' => ACTION_USER_LOGIN,
            'description' => 'User logged in'
        ]);

        $this->setFlash('success', 'Welcome back, ' . $user['name'] . '!');
        $this->redirect('/dashboard');
    }

    /**
     * Show registration form
     */
    public function register() {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }

        $planModel = $this->model('SubscriptionPlan');
        $plans = $planModel->getActivePlans();

        $this->view('auth/register', [
            'title' => 'Register - ' . APP_NAME,
            'plans' => $plans
        ]);
    }

    /**
     * Process registration
     */
    public function registerPost() {
        if (!$this->isPost()) {
            $this->redirect('/auth/register');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/auth/register');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'password' => $this->post('password'),
            'password_confirm' => $this->post('password_confirm'),
            'organization_name' => $this->post('organization_name'),
            'plan_id' => $this->post('plan_id', 1)
        ];

        // Validate input
        $validator = new Validator($data);
        $validator->required('name', 'Name is required')
                  ->required('email', 'Email is required')
                  ->email('email', 'Invalid email address')
                  ->required('password', 'Password is required')
                  ->min('password', 6, 'Password must be at least 6 characters')
                  ->required('password_confirm', 'Please confirm password')
                  ->matches('password', 'password_confirm', 'Passwords do not match')
                  ->required('organization_name', 'Organization name is required');

        if ($validator->fails()) {
            $this->setFlash('error', $validator->getFirstError());
            $this->redirect('/auth/register');
            return;
        }

        // Check if email already exists
        $existing = $this->userModel->findByEmail($data['email']);
        if ($existing) {
            $this->setFlash('error', 'Email already registered');
            $this->redirect('/auth/register');
            return;
        }

        // Create tenant
        $tenantModel = $this->model('Tenant');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['organization_name']));

        // Check if slug exists
        $slugExists = $tenantModel->findBySlug($slug);
        if ($slugExists) {
            $slug .= '-' . uniqid();
        }

        $tenantId = $tenantModel->insert([
            'name' => $data['organization_name'],
            'slug' => $slug,
            'email' => $data['email'],
            'status' => 'active'
        ]);

        // Create user as tenant admin
        $userId = $this->userModel->create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => ROLE_TENANT_ADMIN,
            'status' => 'active'
        ]);

        // Create subscription
        $subscriptionModel = $this->model('TenantSubscription');
        $subscriptionModel->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $data['plan_id'],
            'billing_cycle' => 'monthly',
            'status' => 'trial',
            'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
            'current_period_start' => date('Y-m-d H:i:s'),
            'current_period_end' => date('Y-m-d H:i:s', strtotime('+14 days'))
        ]);

        // Create root folder
        $folderModel = $this->model('Folder');
        $folderModel->createFolder([
            'tenant_id' => $tenantId,
            'name' => 'My Files',
            'created_by' => $userId
        ]);

        // Create API key
        $apiKeyModel = $this->model('ApiKey');
        $apiKeyModel->createKey([
            'tenant_id' => $tenantId,
            'key_name' => 'Default API Key',
            'created_by' => $userId
        ]);

        // Send welcome notification
        $notificationModel = $this->model('Notification');
        $notificationModel->send(
            $tenantId,
            $userId,
            'welcome',
            'Welcome to ' . APP_NAME . '!',
            'Thank you for joining ' . APP_NAME . '. Your account has been created successfully.',
            $data['email']
        );

        // Auto-login user
        $user = $this->userModel->find($userId);
        Auth::login($user);

        $this->setFlash('success', 'Registration successful! Welcome to ' . APP_NAME . '!');
        $this->redirect('/dashboard');
    }

    /**
     * Logout
     */
    public function logout() {
        if (Auth::check()) {
            // Log activity
            $this->activityModel->log([
                'tenant_id' => Auth::tenantId(),
                'user_id' => Auth::id(),
                'action_type' => ACTION_USER_LOGOUT,
                'description' => 'User logged out'
            ]);

            Auth::logout();
        }

        $this->setFlash('success', 'You have been logged out successfully');
        $this->redirect('/auth/login');
    }
}
