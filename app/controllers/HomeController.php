<?php
// FILE: /app/controllers/HomeController.php

/**
 * HomeController
 * Handles home page and landing page
 */
class HomeController extends Controller {
    /**
     * Home page
     */
    public function index() {
        // If user is logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }

        // Load subscription plans
        $planModel = $this->model('SubscriptionPlan');
        $plans = $planModel->getActivePlans();

        $this->view('home/index', [
            'plans' => $plans,
            'title' => 'Welcome to ' . APP_NAME
        ]);
    }
}
