<!-- FILE: /app/views/dashboard/index.php -->
<div class="dashboard">
    <h1>Dashboard</h1>

    <?php if (!empty($warnings)): ?>
        <div class="warnings">
            <?php foreach ($warnings as $warning): ?>
                <div class="alert alert-warning">
                    <?php echo htmlspecialchars($warning); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Storage Used</h3>
            <p class="stat-value"><?php echo Uploader::formatBytes($stats['storage_used']); ?></p>
            <p class="stat-meta">of <?php echo Uploader::formatBytes($tenant['max_storage_bytes']); ?></p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo min($storagePercentage, 100); ?>%"></div>
            </div>
            <p class="progress-text"><?php echo round($storagePercentage, 1); ?>% used</p>
        </div>

        <div class="stat-card">
            <h3>Total Files</h3>
            <p class="stat-value"><?php echo number_format($stats['file_count']); ?></p>
            <p class="stat-meta">of <?php echo number_format($tenant['max_files']); ?> allowed</p>
        </div>

        <div class="stat-card">
            <h3>Total Folders</h3>
            <p class="stat-value"><?php echo number_format($stats['folder_count']); ?></p>
        </div>

        <div class="stat-card">
            <h3>Team Members</h3>
            <p class="stat-value"><?php echo number_format($stats['user_count']); ?></p>
            <p class="stat-meta">of <?php echo number_format($tenant['max_users']); ?> allowed</p>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-section">
            <h2>Recent Files</h2>
            <?php if (empty($recentFiles)): ?>
                <p>No files uploaded yet. <a href="/files">Upload your first file</a></p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFiles as $file): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($file['original_filename']); ?></td>
                                <td><?php echo Uploader::formatBytes($file['file_size']); ?></td>
                                <td><?php echo htmlspecialchars($file['uploaded_by_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($file['created_at'])); ?></td>
                                <td>
                                    <a href="/files/download/<?php echo $file['id']; ?>" class="btn btn-sm">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h2>Recent Activity</h2>
            <?php if (empty($recentActivity)): ?>
                <p>No recent activity</p>
            <?php else: ?>
                <ul class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></strong>
                            <?php echo htmlspecialchars($activity['description']); ?>
                            <span class="activity-time"><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <a href="/files" class="btn btn-primary">Browse Files</a>
        <?php if (Auth::can('manage_users')): ?>
            <a href="/users" class="btn btn-secondary">Manage Users</a>
        <?php endif; ?>
        <?php if (Auth::can('manage_billing')): ?>
            <a href="/subscription" class="btn btn-secondary">View Subscription</a>
        <?php endif; ?>
    </div>
</div>
