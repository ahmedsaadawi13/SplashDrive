<?php
// FILE: /app/controllers/BillingController.php

/**
 * BillingController
 * Handles billing and invoices
 */
class BillingController extends Controller {
    private $invoiceModel;
    private $paymentModel;

    public function __construct() {
        Auth::requirePermission('manage_billing');
        $this->invoiceModel = $this->model('Invoice');
        $this->paymentModel = $this->model('Payment');
    }

    /**
     * Billing overview
     */
    public function index() {
        $tenantId = Auth::tenantId();
        $invoices = $this->invoiceModel->getByTenant($tenantId, 10, 0);
        $payments = $this->paymentModel->getByTenant($tenantId, 10, 0);

        $this->view('billing/index', [
            'title' => 'Billing - ' . APP_NAME,
            'invoices' => $invoices,
            'payments' => $payments
        ]);
    }

    /**
     * View all invoices
     */
    public function invoices() {
        $tenantId = Auth::tenantId();
        $invoices = $this->invoiceModel->getByTenant($tenantId);

        $this->view('billing/invoices', [
            'title' => 'Invoices - ' . APP_NAME,
            'invoices' => $invoices
        ]);
    }

    /**
     * View specific invoice
     */
    public function invoice($invoiceId) {
        $tenantId = Auth::tenantId();
        $invoice = $this->invoiceModel->find($invoiceId);

        if (!$invoice || $invoice['tenant_id'] != $tenantId) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('/billing/invoices');
            return;
        }

        $payment = $this->paymentModel->getByInvoice($invoiceId);

        $this->view('billing/invoice', [
            'title' => 'Invoice #' . $invoice['invoice_number'] . ' - ' . APP_NAME,
            'invoice' => $invoice,
            'payment' => $payment
        ]);
    }

    /**
     * Pay invoice (dummy payment)
     */
    public function pay($invoiceId) {
        if (!$this->isPost()) {
            $this->redirect('/billing/invoices');
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/billing/invoice/' . $invoiceId);
            return;
        }

        $tenantId = Auth::tenantId();
        $invoice = $this->invoiceModel->find($invoiceId);

        if (!$invoice || $invoice['tenant_id'] != $tenantId) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('/billing/invoices');
            return;
        }

        if ($invoice['status'] === 'paid') {
            $this->setFlash('error', 'Invoice already paid');
            $this->redirect('/billing/invoice/' . $invoiceId);
            return;
        }

        // Create payment record (simulated)
        $this->paymentModel->createPayment([
            'tenant_id' => $tenantId,
            'invoice_id' => $invoiceId,
            'payment_method' => 'credit_card',
            'amount' => $invoice['amount'],
            'currency' => $invoice['currency'],
            'status' => 'success'
        ]);

        // Mark invoice as paid
        $this->invoiceModel->markAsPaid($invoiceId);

        $this->setFlash('success', 'Payment processed successfully');
        $this->redirect('/billing/invoice/' . $invoiceId);
    }
}
