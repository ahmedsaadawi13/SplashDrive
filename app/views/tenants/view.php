<!-- FILE: /app/views/tenants/view.php -->
<div class="tenants-page">
    <h1><?php echo htmlspecialchars($tenant['name']); ?></h1>

    <div class="tenant-info">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
        <p><strong>Plan:</strong> <?php echo htmlspecialchars($tenant['plan_name'] ?? 'None'); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($tenant['status']); ?></p>
        <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($tenant['created_at'])); ?></p>
    </div>

    <div class="tenant-stats">
        <h2>Usage Statistics</h2>
        <p>Users: <?php echo number_format($stats['user_count']); ?></p>
        <p>Files: <?php echo number_format($stats['file_count']); ?></p>
        <p>Folders: <?php echo number_format($stats['folder_count']); ?></p>
        <p>Storage: <?php echo Uploader::formatBytes($stats['storage_used']); ?></p>
    </div>

    <a href="/tenants/edit/<?php echo $tenant['id']; ?>" class="btn btn-primary">Edit</a>
    <a href="/tenants" class="btn btn-secondary">Back</a>
</div>
