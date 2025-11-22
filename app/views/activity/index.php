<!-- FILE: /app/views/activity/index.php -->
<div class="activity-page">
    <h1>Activity Log</h1>

    <div class="activity-filters">
        <form method="GET" action="/activity">
            <div class="filter-group">
                <select name="user_id" class="form-control">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo (isset($filters['user_id']) && $filters['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="action_type" class="form-control">
                    <option value="">All Actions</option>
                    <option value="<?php echo ACTION_FILE_UPLOAD; ?>" <?php echo (isset($filters['action_type']) && $filters['action_type'] === ACTION_FILE_UPLOAD) ? 'selected' : ''; ?>>File Upload</option>
                    <option value="<?php echo ACTION_FILE_DOWNLOAD; ?>" <?php echo (isset($filters['action_type']) && $filters['action_type'] === ACTION_FILE_DOWNLOAD) ? 'selected' : ''; ?>>File Download</option>
                    <option value="<?php echo ACTION_FILE_DELETE; ?>" <?php echo (isset($filters['action_type']) && $filters['action_type'] === ACTION_FILE_DELETE) ? 'selected' : ''; ?>>File Delete</option>
                    <option value="<?php echo ACTION_USER_LOGIN; ?>" <?php echo (isset($filters['action_type']) && $filters['action_type'] === ACTION_USER_LOGIN) ? 'selected' : ''; ?>>User Login</option>
                </select>

                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="/activity" class="btn btn-secondary">Clear</a>
                <a href="/activity/export" class="btn btn-secondary">Export CSV</a>
            </div>
        </form>
    </div>

    <?php if (empty($activities)): ?>
        <p>No activity logs found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></td>
                        <td><?php echo htmlspecialchars($activity['action_type']); ?></td>
                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        <td><?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
