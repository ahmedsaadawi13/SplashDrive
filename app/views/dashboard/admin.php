<!-- FILE: /app/views/dashboard/admin.php -->
<div class="admin-dashboard">
    <h1>Platform Admin Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Tenants</h3>
            <p class="stat-value"><?php echo number_format($totalTenants); ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Users</h3>
            <p class="stat-value"><?php echo number_format($totalUsers); ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Storage Used</h3>
            <p class="stat-value"><?php echo Uploader::formatBytes($totalStorage); ?></p>
        </div>
    </div>

    <div class="admin-section">
        <h2>All Tenants</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Plan</th>
                    <th>Users</th>
                    <th>Files</th>
                    <th>Storage Used</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tenant['name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['plan_name'] ?? 'None'); ?></td>
                        <td><?php echo number_format($tenant['user_count']); ?></td>
                        <td><?php echo number_format($tenant['file_count']); ?></td>
                        <td><?php echo Uploader::formatBytes($tenant['storage_used']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($tenant['created_at'])); ?></td>
                        <td>
                            <a href="/tenants/view/<?php echo $tenant['id']; ?>" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-section">
        <h2>Recent Activity</h2>
        <ul class="activity-list">
            <?php foreach ($recentActivity as $activity): ?>
                <li>
                    <strong><?php echo htmlspecialchars($activity['tenant_name'] ?? 'Platform'); ?></strong> -
                    <?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?>:
                    <?php echo htmlspecialchars($activity['description']); ?>
                    <span class="activity-time"><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
