<!-- FILE: /app/views/share/view_file.php -->
<div class="share-page">
    <div class="share-container">
        <h1>Shared File</h1>

        <div class="file-info">
            <div class="file-icon">📄</div>
            <h2><?php echo htmlspecialchars($share['original_filename']); ?></h2>
            <p>Size: <?php echo Uploader::formatBytes($share['file_size']); ?></p>
            <p>Type: <?php echo htmlspecialchars($share['mime_type']); ?></p>
        </div>

        <div class="share-actions">
            <a href="/files/download/<?php echo $share['file_id']; ?>" class="btn btn-primary btn-lg">Download File</a>
        </div>

        <div class="share-info">
            <p>Shared by: <strong><?php echo htmlspecialchars($share['created_by_name']); ?></strong></p>
            <?php if ($share['expires_at']): ?>
                <p>Expires: <?php echo date('M d, Y H:i', strtotime($share['expires_at'])); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
