<!-- FILE: /app/views/billing/index.php -->
<div class="billing-page">
    <h1>Billing</h1>

    <div class="section">
        <h2>Recent Invoices</h2>
        <?php if (empty($invoices)): ?>
            <p>No invoices yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?> <?php echo $invoice['currency']; ?></td>
                            <td><?php echo htmlspecialchars($invoice['status']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                            <td>
                                <a href="/billing/invoice/<?php echo $invoice['id']; ?>" class="btn btn-sm">View</a>
                                <?php if ($invoice['status'] === 'pending'): ?>
                                    <form method="POST" action="/billing/pay/<?php echo $invoice['id']; ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Pay Now</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="/billing/invoices" class="btn btn-secondary">View All Invoices</a>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Recent Payments</h2>
        <?php if (empty($payments)): ?>
            <p>No payments yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['invoice_number']); ?></td>
                            <td>$<?php echo number_format($payment['amount'], 2); ?> <?php echo $payment['currency']; ?></td>
                            <td><?php echo htmlspecialchars($payment['status']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
