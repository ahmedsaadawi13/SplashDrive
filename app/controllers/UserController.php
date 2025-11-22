<?php
// FILE: /app/controllers/UserController.php

/**
 * UserController
 * Handles user management
 */
class UserController extends Controller {
    private $userModel;
    private $tenantModel;

    public function __construct() {
        Auth::requirePermission('manage_users');
        $this->userModel = $this->model('User');
        $this->tenantModel = $this->model('Tenant');
    }

    /**
     * List users
     */
    public function index() {
        $tenantId = Auth::tenantId();
        $users = $this->userModel->getByTenant($tenantId);

        $this->view('users/index', [
            'title' => 'Users - ' . APP_NAME,
            'users' => $users
        ]);
    }

    /**
     * Show create user form
     */
    public function create() {
        $this->view('users/create', [
            'title' => 'Create User - ' . APP_NAME
        ]);
    }

    /**
     * Store new user
     */
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('/users');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/users/create');
            return;
        }

        $tenantId = Auth::tenantId();

        // Check user limit
        if (!$this->tenantModel->canAddUser($tenantId)) {
            $this->setFlash('error', 'User limit reached. Please upgrade your plan.');
            $this->redirect('/users/create');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'password' => $this->post('password'),
            'role' => $this->post('role')
        ];

        // Validate input
        $validator = new Validator($data);
        $validator->required('name')->required('email')->email('email')
                  ->required('password')->min('password', 6)
                  ->required('role')->in('role', [ROLE_TENANT_ADMIN, ROLE_USER, ROLE_READ_ONLY]);

        if ($validator->fails()) {
            $this->setFlash('error', $validator->getFirstError());
            $this->redirect('/users/create');
            return;
        }

        // Check if email exists
        if ($this->userModel->findByEmail($data['email'])) {
            $this->setFlash('error', 'Email already exists');
            $this->redirect('/users/create');
            return;
        }

        // Create user
        $data['tenant_id'] = $tenantId;
        $data['status'] = 'active';
        $this->userModel->create($data);

        $this->setFlash('success', 'User created successfully');
        $this->redirect('/users');
    }

    /**
     * Show edit user form
     */
    public function edit($userId) {
        $tenantId = Auth::tenantId();
        $user = $this->userModel->find($userId);

        if (!$user || $user['tenant_id'] != $tenantId) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        $this->view('users/edit', [
            'title' => 'Edit User - ' . APP_NAME,
            'user' => $user
        ]);
    }

    /**
     * Update user
     */
    public function update($userId) {
        if (!$this->isPost()) {
            $this->redirect('/users');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/users/edit/' . $userId);
            return;
        }

        $tenantId = Auth::tenantId();
        $user = $this->userModel->find($userId);

        if (!$user || $user['tenant_id'] != $tenantId) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'role' => $this->post('role'),
            'status' => $this->post('status')
        ];

        // Validate
        $validator = new Validator($data);
        $validator->required('name')
                  ->required('role')->in('role', [ROLE_TENANT_ADMIN, ROLE_USER, ROLE_READ_ONLY])
                  ->required('status')->in('status', ['active', 'inactive', 'suspended']);

        if ($validator->fails()) {
            $this->setFlash('error', $validator->getFirstError());
            $this->redirect('/users/edit/' . $userId);
            return;
        }

        $this->userModel->update($userId, $data);

        $this->setFlash('success', 'User updated successfully');
        $this->redirect('/users');
    }

    /**
     * Delete user
     */
    public function delete($userId) {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        $tenantId = Auth::tenantId();
        $user = $this->userModel->find($userId);

        if (!$user || $user['tenant_id'] != $tenantId) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        // Prevent deleting yourself
        if ($userId == Auth::id()) {
            $this->json(['success' => false, 'message' => 'Cannot delete yourself'], 400);
            return;
        }

        $this->userModel->delete($userId);

        $this->json(['success' => true, 'message' => 'User deleted successfully']);
    }
}
