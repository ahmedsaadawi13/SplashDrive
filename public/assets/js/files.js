// FILE: /public/assets/js/files.js

/**
 * File Management JavaScript
 */

// Show upload modal
function showUploadModal() {
    document.getElementById('uploadModal').style.display = 'flex';
}

// Close upload modal
function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

// Show create folder modal
function showCreateFolderModal() {
    document.getElementById('createFolderModal').style.display = 'flex';
}

// Close create folder modal
function closeCreateFolderModal() {
    document.getElementById('createFolderModal').style.display = 'none';
}

// Handle file upload
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(uploadForm);
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    document.getElementById('uploadProgress').style.display = 'block';
                    document.getElementById('uploadProgressBar').style.width = percentComplete + '%';
                    document.getElementById('uploadStatus').textContent = 'Uploading... ' + Math.round(percentComplete) + '%';
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showNotification('File uploaded successfully!', 'success');
                        closeUploadModal();
                        location.reload();
                    } else {
                        showNotification(response.message || 'Upload failed', 'error');
                    }
                } else {
                    showNotification('Upload failed', 'error');
                }
                document.getElementById('uploadProgress').style.display = 'none';
            });

            xhr.open('POST', '/files/upload');
            xhr.send(formData);
        });
    }

    // Handle create folder
    const createFolderForm = document.getElementById('createFolderForm');
    if (createFolderForm) {
        createFolderForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(createFolderForm);
            const data = {
                csrf_token: formData.get('csrf_token'),
                name: formData.get('name'),
                parent_id: formData.get('parent_id')
            };

            ajax('/folders/create', 'POST', data, function(err, response) {
                if (err || !response.success) {
                    showNotification(response?.message || 'Failed to create folder', 'error');
                } else {
                    showNotification('Folder created successfully!', 'success');
                    closeCreateFolderModal();
                    location.reload();
                }
            });
        });
    }
});

// Delete file
function deleteFile(fileId) {
    confirmAction('Are you sure you want to delete this file?', function() {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        ajax('/files/delete/' + fileId, 'POST', { csrf_token: csrfToken }, function(err, response) {
            if (err || !response.success) {
                showNotification(response?.message || 'Failed to delete file', 'error');
            } else {
                showNotification('File deleted successfully', 'success');
                location.reload();
            }
        });
    });
}

// Delete folder
function deleteFolder(folderId) {
    confirmAction('Are you sure you want to delete this folder? All contents will be moved to trash.', function() {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        ajax('/folders/delete/' + folderId, 'POST', { csrf_token: csrfToken }, function(err, response) {
            if (err || !response.success) {
                showNotification(response?.message || 'Failed to delete folder', 'error');
            } else {
                showNotification('Folder deleted successfully', 'success');
                location.reload();
            }
        });
    });
}

// Rename file
function renameFile(fileId, currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        ajax('/files/rename/' + fileId, 'POST', {
            csrf_token: csrfToken,
            name: newName
        }, function(err, response) {
            if (err || !response.success) {
                showNotification(response?.message || 'Failed to rename file', 'error');
            } else {
                showNotification('File renamed successfully', 'success');
                location.reload();
            }
        });
    }
}

// Rename folder
function renameFolder(folderId, currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        ajax('/folders/rename/' + folderId, 'POST', {
            csrf_token: csrfToken,
            name: newName
        }, function(err, response) {
            if (err || !response.success) {
                showNotification(response?.message || 'Failed to rename folder', 'error');
            } else {
                showNotification('Folder renamed successfully', 'success');
                location.reload();
            }
        });
    }
}

// Create share link
function createShare(type, itemId) {
    const expiresIn = prompt('Link expires in (days, leave empty for never):', '30');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    ajax('/share/create', 'POST', {
        csrf_token: csrfToken,
        type: type,
        item_id: itemId,
        expires_in: expiresIn
    }, function(err, response) {
        if (err || !response.success) {
            showNotification(response?.message || 'Failed to create share link', 'error');
        } else {
            prompt('Share link created! Copy this URL:', response.share_url);
        }
    });
}
