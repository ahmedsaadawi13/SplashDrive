// FILE: /public/assets/js/shares.js

/**
 * Share Management JavaScript
 */

// Delete share link
function deleteShare(shareId) {
    confirmAction('Are you sure you want to delete this share link?', function() {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        ajax('/share/delete/' + shareId, 'POST', { csrf_token: csrfToken }, function(err, response) {
            if (err || !response.success) {
                showNotification(response?.message || 'Failed to delete share link', 'error');
            } else {
                showNotification('Share link deleted successfully', 'success');
                location.reload();
            }
        });
    });
}
