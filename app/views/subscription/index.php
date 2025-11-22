<!-- FILE: /app/views/subscription/index.php -->
<div class="subscription-page">
    <h1>Subscription</h1>

    <div class="current-plan">
        <h2>Current Plan: <?php echo htmlspecialchars($subscription['plan_name']); ?></h2>
        <p>Status: <strong><?php echo htmlspecialchars($subscription['status']); ?></strong></p>
        <p>Billing Cycle: <?php echo htmlspecialchars($subscription['billing_cycle']); ?></p>
        <?php if ($subscription['current_period_end']): ?>
            <p>Current Period Ends: <?php echo date('M d, Y', strtotime($subscription['current_period_end'])); ?></p>
        <?php endif; ?>
    </div>

    <div class="plan-limits">
        <h3>Plan Limits</h3>
        <ul>
            <li>Max Storage: <?php echo Uploader::formatBytes($subscription['max_storage_bytes']); ?></li>
            <li>Max Users: <?php echo number_format($subscription['max_users']); ?></li>
            <li>Max Files: <?php echo number_format($subscription['max_files']); ?></li>
        </ul>
    </div>

    <div class="current-usage">
        <h3>Current Usage</h3>
        <ul>
            <li>Storage Used: <?php echo Uploader::formatBytes($stats['storage_used']); ?> (<?php echo round(($stats['storage_used'] / $subscription['max_storage_bytes']) * 100, 1); ?>%)</li>
            <li>Users: <?php echo number_format($stats['user_count']); ?> / <?php echo number_format($subscription['max_users']); ?></li>
            <li>Files: <?php echo number_format($stats['file_count']); ?> / <?php echo number_format($subscription['max_files']); ?></li>
        </ul>
    </div>

    <a href="/subscription/plans" class="btn btn-primary">Change Plan</a>
    <a href="/billing" class="btn btn-secondary">View Billing</a>
</div>
