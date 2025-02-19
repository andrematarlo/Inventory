// Global SweetAlert configurations and functions
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

// Success Toast
function showSuccess(message) {
    Toast.fire({
        icon: 'success',
        title: message
    });
}

// Error Toast
function showError(message) {
    Toast.fire({
        icon: 'error',
        title: message
    });
}

// Confirmation Dialog
function confirmAction(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, do it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

// Delete Confirmation
function confirmDelete(callback) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

// Form Validation Error
function showValidationErrors(errors) {
    let errorMessage = '<ul class="list-unstyled">';
    Object.values(errors).forEach(error => {
        errorMessage += `<li>${error}</li>`;
    });
    errorMessage += '</ul>';

    Swal.fire({
        title: 'Validation Error',
        html: errorMessage,
        icon: 'error'
    });
} 