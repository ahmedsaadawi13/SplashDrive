// FILE: /public/assets/js/main.js

/**
 * SplashDrive Main JavaScript
 * Common functions used throughout the application
 */

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Helper function to make AJAX requests
function ajax(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);

    if (method === 'POST') {
        xhr.setRequestHeader('Content-Type', 'application/json');
    }

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                callback(null, response);
            } catch (e) {
                callback(null, { success: true, data: xhr.responseText });
            }
        } else {
            callback(new Error('Request failed: ' + xhr.status), null);
        }
    };

    xhr.onerror = function() {
        callback(new Error('Network error'), null);
    };

    if (method === 'POST' && data) {
        xhr.send(JSON.stringify(data));
    } else {
        xhr.send();
    }
}

// Show notification
function showNotification(message, type) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-' + type;
    alert.textContent = message;

    const main = document.querySelector('.app-main');
    if (main) {
        main.insertBefore(alert, main.firstChild);

        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    }
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}
