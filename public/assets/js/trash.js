// FILE: /public/assets/js/trash.js

/**
 * Trash Management JavaScript
 */

// Restore item from trash
function restoreItem(type, itemId) {
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    ajax('/trash/restore/' + type + '/' + itemId, 'POST', { csrf_token: csrfToken }, function(err, response) {
        if (err || !response.success) {
            showNotification(response?.message || 'Failed to restore item', 'error');
        } else {
            showNotification('Item restored successfully', 'success');
            location.reload();
        }
    });
}

// Delete item permanently
function deleteItem(type, itemId) {
    confirmAction('Are you sure you want to PERMANENTLY delete this item? This cannot be undone!', function() {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        ajax('/trash/delete/' + type + '/' + itemId, 'POST', { csrf_token: csrfToken }, function(err, response) {
            if (err || !response.success) {
                showNotification(response?.message || 'Failed to delete item', 'error');
            } else {
                showNotification('Item deleted permanently', 'success');
                location.reload();
            }
        });
    });
}

// Empty trash
function emptyTrash() {
    confirmAction('Are you sure you want to PERMANENTLY delete ALL items in trash? This cannot be undone!', function() {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        ajax('/trash/empty', 'POST', { csrf_token: csrfToken }, function(err, response) {
            if (err || !response.success) {
                showNotification(response?.message || 'Failed to empty trash', 'error');
            } else {
                showNotification('Trash emptied successfully', 'success');
                location.reload();
            }
        });
    });
}
