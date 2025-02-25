<!-- Import Employees Modal -->
<div class="modal fade" id="importEmployeesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Excel File Requirements:</h6>
                        <ul class="mb-0">
                            <li>Required column headers (any of these variations will work):
                                <ul>
                                    <li>Name: "Name", "Full Name", "Employee Name"</li>
                                    <li>Email: "Email", "Email Address", "Mail"</li>
                                    <li>Gender: "Gender", "Sex"</li>
                                    <li>Role: "Role", "Position", "Designation"</li>
                                    <li>Address: "Address", "Location", "Home Address"</li>
                                </ul>
                            </li>
                            <li>Email must be Gmail (@gmail.com)</li>
                            <li>Gender must be "Male", "Female", "M", or "F"</li>
                            <li>Role must match one of: {{ $roles->pluck('RoleName')->implode(', ') }}</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Upload Excel File</label>
                        <input type="file" 
                               class="form-control" 
                               id="excel_file" 
                               name="excel_file" 
                               accept=".xlsx,.xls" 
                               required>
                        <div class="form-text">Only Excel files (.xlsx, .xls) are allowed</div>
                    </div>

                    <a href="{{ asset('templates/employee_import_template.xlsx') }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const importForm = $('#importForm');
    const importBtn = $('#importBtn');

    importForm.on('submit', function(e) {
        e.preventDefault();
        
        if (!$('#excel_file').val()) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a file first',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Importing...',
            text: 'Please wait while we process your file',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            title: response.success ? 'Success!' : 'Error!',
                            text: response.message,
                            icon: response.icon || (response.success ? 'success' : 'error'),
                            confirmButtonText: 'OK',
                            confirmButtonColor: response.success ? '#28a745' : '#dc3545'
                        }).then((result) => {
                            if (result.isConfirmed && response.success) {
                                window.location.reload();
                            }
                        });
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'An error occurred during import',
                            icon: response.icon || 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // Reset form when modal is closed
    $('#importEmployeesModal').on('hidden.bs.modal', function() {
        importForm[0].reset();
        importBtn.prop('disabled', false)
            .html('<i class="bi bi-upload"></i> Import');
        Swal.close();
    });
});
</script>
@endpush 