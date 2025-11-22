<!-- FILE: /app/views/share/index.php -->
<div class="shares-page">
    <h1>Shared Links</h1>

    <?php if (empty($shares)): ?>
        <p>No shared links yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Created By</th>
                    <th>Expires</th>
                    <th>Access Count</th>
                    <th>Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shares as $share): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($share['original_filename'] ?? $share['folder_name']); ?></td>
                        <td><?php echo htmlspecialchars($share['share_type']); ?></td>
                        <td><?php echo htmlspecialchars($share['created_by_name']); ?></td>
                        <td><?php echo $share['expires_at'] ? date('M d, Y', strtotime($share['expires_at'])) : 'Never'; ?></td>
                        <td><?php echo $share['access_count']; ?></td>
                        <td>
                            <input type="text" value="<?php echo APP_URL . '/share/' . $share['token']; ?>" class="form-control" readonly onclick="this.select()">
                        </td>
                        <td>
                            <button onclick="deleteShare(<?php echo $share['id']; ?>)" class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="/files" class="btn btn-secondary">Back to Files</a>
</div>

<script src="/assets/js/shares.js"></script>
