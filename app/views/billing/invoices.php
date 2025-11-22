<!-- FILE: /app/views/billing/invoices.php -->
<div class="billing-page">
    <h1>All Invoices</h1>

    <table class="table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Paid At</th>
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
                    <td><?php echo $invoice['paid_at'] ? date('M d, Y', strtotime($invoice['paid_at'])) : '-'; ?></td>
                    <td>
                        <a href="/billing/invoice/<?php echo $invoice['id']; ?>" class="btn btn-sm">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="/billing" class="btn btn-secondary">Back to Billing</a>
</div>
