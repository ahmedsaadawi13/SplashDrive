<?php
// FILE: /app/controllers/SubscriptionController.php

/**
 * SubscriptionController
 * Handles subscription management
 */
class SubscriptionController extends Controller {
    private $subscriptionModel;
    private $planModel;
    private $tenantModel;

    public function __construct() {
        Auth::requirePermission('manage_billing');
        $this->subscriptionModel = $this->model('TenantSubscription');
        $this->planModel = $this->model('SubscriptionPlan');
        $this->tenantModel = $this->model('Tenant');
    }

    /**
     * View current subscription
     */
    public function index() {
        $tenantId = Auth::tenantId();
        $subscription = $this->subscriptionModel->getByTenant($tenantId);
        $stats = $this->tenantModel->getStatistics($tenantId);

        $this->view('subscription/index', [
            'title' => 'Subscription - ' . APP_NAME,
            'subscription' => $subscription,
            'stats' => $stats
        ]);
    }

    /**
     * View available plans
     */
    public function plans() {
        $plans = $this->planModel->getActivePlans();
        $tenantId = Auth::tenantId();
        $currentSubscription = $this->subscriptionModel->getByTenant($tenantId);

        $this->view('subscription/plans', [
            'title' => 'Subscription Plans - ' . APP_NAME,
            'plans' => $plans,
            'currentSubscription' => $currentSubscription
        ]);
    }

    /**
     * Change subscription plan
     */
    public function change() {
        if (!$this->isPost()) {
            $this->redirect('/subscription/plans');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/subscription/plans');
            return;
        }

        $tenantId = Auth::tenantId();
        $planId = $this->post('plan_id');
        $billingCycle = $this->post('billing_cycle', 'monthly');

        // Validate plan
        $plan = $this->planModel->find($planId);
        if (!$plan) {
            $this->setFlash('error', 'Invalid plan');
            $this->redirect('/subscription/plans');
            return;
        }

        // Change plan
        $this->subscriptionModel->changePlan($tenantId, $planId, $billingCycle);

        $this->setFlash('success', 'Subscription plan changed successfully');
        $this->redirect('/subscription');
    }
}
