/* 
 * Main application JavaScript file
 * This file is loaded by the application layout and provides common functionality
 */

// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize alerts dismissal
    var alertList = [].slice.call(document.querySelectorAll('.alert'));
    alertList.map(function (alertEl) {
        return new bootstrap.Alert(alertEl);
    });
});

// CSRF token setup for AJAX requests
if (typeof window.Laravel === 'undefined') {
    window.Laravel = {};
}

// Set CSRF token for AJAX requests
window.Laravel.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// Add CSRF token to all AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.axios !== 'undefined') {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }
    
    // Also set it up for jQuery if available
    if (typeof window.jQuery !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        });
    }
}); 