<!-- FILE: /app/views/billing/invoice.php -->
<div class="billing-page">
    <h1>Invoice #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>

    <div class="invoice-details">
        <p><strong>Amount:</strong> $<?php echo number_format($invoice['amount'], 2); ?> <?php echo $invoice['currency']; ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($invoice['status']); ?></p>
        <p><strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($invoice['description'] ?? 'N/A'); ?></p>
        <?php if ($invoice['paid_at']): ?>
            <p><strong>Paid At:</strong> <?php echo date('M d, Y H:i', strtotime($invoice['paid_at'])); ?></p>
        <?php endif; ?>
    </div>

    <?php if ($payment): ?>
        <div class="payment-info">
            <h3>Payment Information</h3>
            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($payment['status']); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($invoice['status'] === 'pending'): ?>
        <form method="POST" action="/billing/pay/<?php echo $invoice['id']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
            <button type="submit" class="btn btn-primary">Pay Invoice</button>
        </form>
    <?php endif; ?>

    <a href="/billing" class="btn btn-secondary">Back to Billing</a>
</div>
