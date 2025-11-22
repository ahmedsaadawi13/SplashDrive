<!-- FILE: /app/views/tenants/index.php -->
<div class="tenants-page">
    <div class="page-header">
        <h1>Tenants</h1>
        <a href="/tenants/create" class="btn btn-primary">Add Tenant</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Organization</th>
                <th>Plan</th>
                <th>Users</th>
                <th>Files</th>
                <th>Storage</th>
                <th>Status</th>
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
                    <td><?php echo htmlspecialchars($tenant['status']); ?></td>
                    <td>
                        <a href="/tenants/view/<?php echo $tenant['id']; ?>" class="btn btn-sm">View</a>
                        <a href="/tenants/edit/<?php echo $tenant['id']; ?>" class="btn btn-sm">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
