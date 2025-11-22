<?php
// FILE: /app/controllers/TenantController.php

/**
 * TenantController
 * Handles tenant management (platform admin only)
 */
class TenantController extends Controller {
    private $tenantModel;

    public function __construct() {
        Auth::requireRole(ROLE_PLATFORM_ADMIN);
        $this->tenantModel = $this->model('Tenant');
    }

    /**
     * List all tenants
     */
    public function index() {
        $tenants = $this->tenantModel->getAllWithStats();

        $this->view('tenants/index', [
            'title' => 'Tenants - ' . APP_NAME,
            'tenants' => $tenants
        ]);
    }

    /**
     * Show create tenant form
     */
    public function create() {
        $planModel = $this->model('SubscriptionPlan');
        $plans = $planModel->getActivePlans();

        $this->view('tenants/create', [
            'title' => 'Create Tenant - ' . APP_NAME,
            'plans' => $plans
        ]);
    }

    /**
     * Store new tenant
     */
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('/tenants');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/tenants/create');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'address' => $this->post('address')
        ];

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']));
        if ($this->tenantModel->findBySlug($slug)) {
            $slug .= '-' . uniqid();
        }

        $data['slug'] = $slug;
        $data['status'] = 'active';

        $tenantId = $this->tenantModel->insert($data);

        $this->setFlash('success', 'Tenant created successfully');
        $this->redirect('/tenants');
    }

    /**
     * Show edit tenant form
     */
    public function edit($tenantId) {
        $tenant = $this->tenantModel->getWithSubscription($tenantId);

        if (!$tenant) {
            $this->setFlash('error', 'Tenant not found');
            $this->redirect('/tenants');
            return;
        }

        $this->view('tenants/edit', [
            'title' => 'Edit Tenant - ' . APP_NAME,
            'tenant' => $tenant
        ]);
    }

    /**
     * Update tenant
     */
    public function update($tenantId) {
        if (!$this->isPost()) {
            $this->redirect('/tenants');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/tenants/edit/' . $tenantId);
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'address' => $this->post('address'),
            'status' => $this->post('status')
        ];

        $this->tenantModel->update($tenantId, $data);

        $this->setFlash('success', 'Tenant updated successfully');
        $this->redirect('/tenants');
    }

    /**
     * View tenant details
     */
    public function view($tenantId) {
        $tenant = $this->tenantModel->getWithSubscription($tenantId);

        if (!$tenant) {
            $this->setFlash('error', 'Tenant not found');
            $this->redirect('/tenants');
            return;
        }

        $stats = $this->tenantModel->getStatistics($tenantId);

        $this->view('tenants/view', [
            'title' => $tenant['name'] . ' - ' . APP_NAME,
            'tenant' => $tenant,
            'stats' => $stats
        ]);
    }
}
