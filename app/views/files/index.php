<!-- FILE: /app/views/files/index.php -->
<div class="files-page">
    <div class="page-header">
        <h1>My Files</h1>
        <div class="page-actions">
            <button onclick="showUploadModal()" class="btn btn-primary">Upload File</button>
            <button onclick="showCreateFolderModal()" class="btn btn-secondary">New Folder</button>
            <a href="/trash" class="btn btn-secondary">Trash</a>
            <a href="/shares" class="btn btn-secondary">Shared Links</a>
        </div>
    </div>

    <!-- Breadcrumb -->
    <?php if (!empty($breadcrumb)): ?>
        <div class="breadcrumb">
            <a href="/files">Home</a>
            <?php foreach ($breadcrumb as $crumb): ?>
                <span class="separator">/</span>
                <a href="/files/folder/<?php echo $crumb['id']; ?>"><?php echo htmlspecialchars($crumb['name']); ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Folders -->
    <?php if (!empty($folders)): ?>
        <div class="section">
            <h2>Folders</h2>
            <div class="items-grid">
                <?php foreach ($folders as $folder): ?>
                    <div class="item-card folder-card">
                        <div class="item-icon">📁</div>
                        <div class="item-name">
                            <a href="/files/folder/<?php echo $folder['id']; ?>">
                                <?php echo htmlspecialchars($folder['name']); ?>
                            </a>
                        </div>
                        <div class="item-meta">
                            <?php echo $folder['file_count']; ?> files, <?php echo $folder['subfolder_count']; ?> folders
                        </div>
                        <div class="item-actions">
                            <button onclick="renameFolder(<?php echo $folder['id']; ?>, '<?php echo htmlspecialchars($folder['name'], ENT_QUOTES); ?>')" class="btn btn-sm">Rename</button>
                            <button onclick="deleteFolder(<?php echo $folder['id']; ?>)" class="btn btn-sm btn-danger">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Files -->
    <div class="section">
        <h2>Files</h2>
        <?php if (empty($files)): ?>
            <p>No files in this folder. Upload your first file!</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Type</th>
                        <th>Uploaded By</th>
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
                            <td><?php echo htmlspecialchars($file['uploaded_by_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($file['created_at'])); ?></td>
                            <td>
                                <a href="/files/download/<?php echo $file['id']; ?>" class="btn btn-sm">Download</a>
                                <button onclick="createShare('file', <?php echo $file['id']; ?>)" class="btn btn-sm">Share</button>
                                <button onclick="renameFile(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['original_filename'], ENT_QUOTES); ?>')" class="btn btn-sm">Rename</button>
                                <button onclick="deleteFile(<?php echo $file['id']; ?>)" class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close" onclick="closeUploadModal()">&times;</span>
        <h2>Upload File</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
            <input type="hidden" name="folder_id" value="<?php echo $currentFolder['id'] ?? ''; ?>">
            <div class="form-group">
                <label for="file">Choose File</label>
                <input type="file" id="file" name="file" required class="form-control">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Upload</button>
                <button type="button" onclick="closeUploadModal()" class="btn btn-secondary">Cancel</button>
            </div>
            <div id="uploadProgress" style="display: none;">
                <div class="progress-bar">
                    <div id="uploadProgressBar" class="progress-fill" style="width: 0%"></div>
                </div>
                <p id="uploadStatus">Uploading...</p>
            </div>
        </form>
    </div>
</div>

<!-- Create Folder Modal -->
<div id="createFolderModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close" onclick="closeCreateFolderModal()">&times;</span>
        <h2>Create Folder</h2>
        <form id="createFolderForm">
            <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
            <input type="hidden" name="parent_id" value="<?php echo $currentFolder['id'] ?? ''; ?>">
            <div class="form-group">
                <label for="folderName">Folder Name</label>
                <input type="text" id="folderName" name="name" required class="form-control">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create</button>
                <button type="button" onclick="closeCreateFolderModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/files.js"></script>
