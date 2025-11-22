<!-- FILE: /app/views/files/search.php -->
<div class="files-page">
    <h1>Search Results</h1>
    <p>Search query: <strong><?php echo htmlspecialchars($query); ?></strong></p>

    <?php if (empty($files)): ?>
        <p>No files found matching your search.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Type</th>
                    <th>Folder</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['original_filename']); ?></td>
                        <td><?php echo Uploader::formatBytes($file['file_size']); ?></td>
                        <td><?php echo htmlspecialchars($file['extension']); ?></td>
                        <td><?php echo htmlspecialchars($file['folder_name'] ?? 'Root'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($file['created_at'])); ?></td>
                        <td>
                            <a href="/files/download/<?php echo $file['id']; ?>" class="btn btn-sm">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="/files" class="btn btn-secondary">Back to Files</a>
</div>
