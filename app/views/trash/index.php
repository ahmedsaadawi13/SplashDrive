<!-- FILE: /app/views/trash/index.php -->
<div class="trash-page">
    <div class="page-header">
        <h1>Trash</h1>
        <?php if (Auth::can('delete_permanently')): ?>
            <button onclick="emptyTrash()" class="btn btn-danger">Empty Trash</button>
        <?php endif; ?>
    </div>

    <h2>Deleted Folders</h2>
    <?php if (empty($deletedFolders)): ?>
        <p>No deleted folders</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Deleted By</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deletedFolders as $folder): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($folder['name']); ?></td>
                        <td><?php echo htmlspecialchars($folder['deleted_by_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($folder['deleted_at'])); ?></td>
                        <td>
                            <button onclick="restoreItem('folder', <?php echo $folder['id']; ?>)" class="btn btn-sm">Restore</button>
                            <?php if (Auth::can('delete_permanently')): ?>
                                <button onclick="deleteItem('folder', <?php echo $folder['id']; ?>)" class="btn btn-sm btn-danger">Delete Forever</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Deleted Files</h2>
    <?php if (empty($deletedFiles)): ?>
        <p>No deleted files</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Deleted By</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deletedFiles as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['original_filename']); ?></td>
                        <td><?php echo Uploader::formatBytes($file['file_size']); ?></td>
                        <td><?php echo htmlspecialchars($file['deleted_by_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($file['deleted_at'])); ?></td>
                        <td>
                            <button onclick="restoreItem('file', <?php echo $file['id']; ?>)" class="btn btn-sm">Restore</button>
                            <?php if (Auth::can('delete_permanently')): ?>
                                <button onclick="deleteItem('file', <?php echo $file['id']; ?>)" class="btn btn-sm btn-danger">Delete Forever</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="/files" class="btn btn-secondary">Back to Files</a>
</div>

<script src="/assets/js/trash.js"></script>
