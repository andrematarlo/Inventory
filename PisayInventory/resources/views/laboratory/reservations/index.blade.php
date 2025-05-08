@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Laboratory Reservations</h2>
    
    <div class="button-container">
        <a href="{{ route('laboratory.reservations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Book Laboratory
        </a>
    </div>


    <div class="card">
        <div class="card-body">
            <!-- Status Toggle Buttons -->
            <div class="btn-group mb-4 w-100">
                <button type="button" class="btn btn-outline-primary status-toggle" data-status="Approval of Teacher">
                    Approval of Teacher <span class="badge bg-primary ms-2" id="teacherApprovalCount">0</span>
                </button>
                <button type="button" class="btn btn-outline-warning status-toggle active" data-status="Approval of SRA / SRS">
                    Approval of SRA / SRS <span class="badge bg-warning ms-2" id="sraSrsApprovalCount">0</span>
                </button>
                <button type="button" class="btn btn-outline-success status-toggle" data-status="Approved">
                    Approved <span class="badge bg-success ms-2" id="approvedCount">0</span>
                </button>
                <button type="button" class="status-toggle btn btn-outline-danger" data-status="Disapproved">
                    Disapproved <span class="badge bg-danger" id="disapprovedCount">0</span>
                </button>
            </div>

            <!-- Search and Entries Control -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    Show 
                    <select class="form-select form-select-sm d-inline-block w-auto" id="entriesPerPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    entries
                    </div>
                    <div class="search-box">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                </div>
            </div>

            <!-- Reservations Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="min-width: 120px">Actions</th>
                            <th style="min-width: 150px">Control No.</th>
                            <th style="min-width: 150px">Laboratory</th>
                            <th style="min-width: 150px">Grade/Section</th>
                            <th style="min-width: 170px">Subject</th>
                            <th style="min-width: 200px">Teacher</th>
                            <th style="min-width: 120px">Date</th>
                            <th style="min-width: 170px">Time</th>
                            <th style="min-width: 150px">Requested By</th>
                        </tr>
                    </thead>
                    <tbody id="reservationsTableBody">
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing <span id="recordsShowing">0 to 0</span> of <span id="totalRecords">0</span> entries
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination" id="pagination">
                        <!-- Pagination will be loaded dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>

.page-title {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .button-container {
        text-align: right;
        margin-bottom: 2rem;
    }

.table {
    width: 100%;
    white-space: nowrap;  /* Prevents text wrapping */
}
.table-responsive {
    overflow-x: auto;
    min-width: 100%;
}

.status-toggle {
    flex: 1;
    padding: 1rem;
    font-weight: 500;
}

.status-toggle.active {
    font-weight: bold;
}

    .btn-group {
    gap: 10px;
}

.search-box {
    width: 300px;
}

.badge {
    font-size: 0.9rem;
}

.table th, .table td {
    background-color: #f8f9fa;
    padding: 0.75rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

}

.action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    border-radius: 4px;
    margin: 0 2px;
}

.btn-group {
    display: flex;
    gap: 3px;
    justify-content: center;
}

.btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

</style>
@endsection

@push('scripts')
<script>
    // Get user data from PHP with detailed role debugging
    const userRole = '{{ Auth::user()->role }}'.trim();
    const userId = '{{ Auth::id() }}';
    const userEmployeeId = '{{ Auth::user()->employee ? Auth::user()->employee->EmployeeID : "null" }}';
    const isAdmin = '{{ Auth::user()->role === "Admin" ? "true" : "false" }}';
    const userPermissions = JSON.parse('{!! addslashes(json_encode($userPermissions)) !!}');
    
    // Debug role information
    console.log('Detailed Role Information:', {
        rawRole: '{{ Auth::user()->role }}',
        trimmedRole: userRole,
        roleLength: userRole.length,
        roleCharCodes: Array.from(userRole).map(char => char.charCodeAt(0)),
        isSRS: userRole === 'SRS',
        isSRA: userRole === 'SRA',
        exactMatch: {
            SRS: userRole === 'SRS',
            SRA: userRole === 'SRA'
        }
    });

$(document).ready(function() {
    let currentStatus = 'Approval of SRA / SRS';
    let currentPage = 1;
    let searchQuery = '';
    let entriesPerPage = 10;

    // Add detailed role debugging at startup
    console.log('Initial User Role Debug:', {
        userRole: userRole,
        isSRSorSRA: userRole === 'SRS' || userRole === 'SRA',
        rawUserRole: '{{ Auth::user()->role }}',
        isAdmin: isAdmin,
        userId: userId,
        userEmployeeId: userEmployeeId
    });

    // Initial load
    loadReservations();
    loadCounts();

    // Status toggle click handler
    $('.status-toggle').click(function() {
        $('.status-toggle').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status');
        currentPage = 1;
        
        // Add debug logging
        console.log('Status changed to:', currentStatus);
        
        loadReservations();
    });

    // Search input handler
    $('#searchInput').on('input', debounce(function() {
        searchQuery = $(this).val();
        currentPage = 1;
        loadReservations();
    }, 500));

    // Entries per page change handler
    $('#entriesPerPage').change(function() {
        entriesPerPage = $(this).val();
        currentPage = 1;
        loadReservations();
    });

    // Load reservations
    function loadReservations() {
        console.log('Loading reservations for status:', currentStatus);
        
        $.ajax({
            url: "{{ route('laboratory.reservations.data') }}",
            data: {
                status: currentStatus,
                page: currentPage,
                search: searchQuery,
                per_page: entriesPerPage
            },
            success: function(response) {
                console.log('Reservations loaded:', {
                    status: currentStatus,
                    count: response.data.length,
                    total: response.meta.total,
                    data: response.data,
                    // Add detailed logging for each reservation
                    reservations: response.data.map(r => ({
                        id: r.reservation_id,
                        status: r.status,
                        requested_by: r.requested_by,
                        requested_by_role: r.requested_by_role,
                        is_student: r.requested_by_role !== 'Teacher'
                    }))
                });
                
                renderTable(response.data);
                renderPagination(response.meta);
                updateShowingEntries(response.meta);
            },
            error: function(xhr, status, error) {
                console.error('Error loading reservations:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Load status counts
    function loadCounts() {
        $.get("{{ route('laboratory.reservations.counts') }}", function(response) {
            $('#teacherApprovalCount').text(response.teacherApproval || 0);
            $('#sraSrsApprovalCount').text(response.sraSrsApproval || 0);
            $('#approvedCount').text(response.approved || 0);
            $('#cancelledCount').text(response.cancelled || 0);
            $('#disapprovedCount').text(response.disapproved || 0);
        });
    }

    // Render table
    function renderTable(reservations) {
        let html = '';
        
        if (!reservations || reservations.length === 0) {
            html = '<tr><td colspan="9" class="text-center">No reservations found</td></tr>';
        } else {
            reservations.forEach(function(reservation) {
                // Check if the current user is a teacher
                const isTeacher = userRole === 'Teacher';
                // Check if the current user is SRA/SRS
                const isSRAorSRS = userRole && (userRole.trim().toUpperCase() === 'SRS' || userRole.trim().toUpperCase() === 'SRA');
                // Check if the reservation was made by a teacher (case-insensitive)
                const isTeacherReservation = reservation.requested_by_role && reservation.requested_by_role.toLowerCase() === 'teacher';
                
                console.log('Rendering reservation:', {
                    id: reservation.reservation_id,
                    status: reservation.status,
                    requested_by: reservation.requested_by,
                    requested_by_role: reservation.requested_by_role,
                    is_teacher_reservation: isTeacherReservation,
                    current_status: currentStatus,
                    user_role: userRole,
                    is_teacher: isTeacher,
                    is_sra_srs: isSRAorSRS
                });
                
                html += `
                    <tr>
                        <td>
                            <div class="btn-group">
                                <!-- View button - visible to all -->
                                <button type="button" class="btn btn-info btn-sm view-reservation" 
                                        data-id="${reservation.reservation_id}" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>

                                ${(() => {
                                    // If it's a student reservation in "Approval of Teacher" status and user is a teacher
                                    if (!isTeacherReservation && reservation.status === 'Approval of Teacher' && isTeacher) {
                                        console.log('Showing teacher approval buttons for:', reservation.reservation_id);
                                        return `
                                            <!-- Approve button for teachers -->
                                            <button type="button" class="btn btn-success btn-sm approve-reservation" 
                                                    data-id="${reservation.reservation_id}" title="Approve">
                                                <i class="bi bi-check-lg"></i>
                                            </button>

                                            <!-- Disapprove button for teachers -->
                                            <button type="button" class="btn btn-danger btn-sm disapprove-reservation" 
                                                    data-id="${reservation.reservation_id}" title="Disapprove">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        `;
                                    }
                                    
                                    // If it's in "Approval of SRA / SRS" status and user is SRA/SRS
                                    if (reservation.status === 'Approval of SRA / SRS' && isSRAorSRS) {
                                        console.log('Showing SRA/SRS approval buttons for:', reservation.reservation_id);
                                        return `
                                            <!-- Approve button for SRA/SRS -->
                                            <button type="button" class="btn btn-success btn-sm approve-reservation" 
                                                    data-id="${reservation.reservation_id}" title="Approve">
                                                <i class="bi bi-check-lg"></i>
                                            </button>

                                            <!-- Disapprove button for SRA/SRS -->
                                            <button type="button" class="btn btn-danger btn-sm disapprove-reservation" 
                                                    data-id="${reservation.reservation_id}" title="Disapprove">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        `;
                                    }

                                    // Cancel button - show for both teachers and SRA/SRS
                                    if ((isTeacher || isSRAorSRS) && reservation.status !== 'Approved' && reservation.status !== 'Disapproved') {
                                        return `
                                            <button type="button" class="btn btn-warning btn-sm cancel-reservation" 
                                                    data-id="${reservation.reservation_id}" title="Cancel">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        `;
                                    }

                                    return '';
                                })()}
                            </div>
                        </td>
                        <td>${reservation.control_no || '-'}</td>
                        <td>${reservation.laboratory ? reservation.laboratory.laboratory_name : '-'}</td>
                        <td>${reservation.grade_section || '-'}</td>
                        <td>${reservation.subject || '-'}</td>
                        <td>${reservation.teacher ? `${reservation.teacher.FirstName} ${reservation.teacher.LastName}` : '-'}</td>
                        <td>${
                          reservation.reservation_date_from
                            ? (reservation.reservation_date_to && reservation.reservation_date_to !== reservation.reservation_date_from
                                ? formatDate(reservation.reservation_date_from) + ' to ' + formatDate(reservation.reservation_date_to)
                                : formatDate(reservation.reservation_date_from))
                            : '-'
                        }</td>
                        <td>${formatTime(reservation.start_time)} - ${formatTime(reservation.end_time)}</td>
                        <td>${reservation.requested_by || '-'}</td>
                    </tr>
                `;
            });
        }
        
        $('#reservationsTableBody').html(html);
    }

    // Handle endorsement
    $(document).on('click', '.endorse-reservation', function() {
        const fullReservationId = $(this).data('id');
        // Log the full ID for debugging
        console.log('Full Reservation ID:', fullReservationId);
        
        // Extract just the numeric part if it's a RES-prefixed ID
        const reservationId = fullReservationId.replace('RES', '');
        console.log('Processed Reservation ID:', reservationId);
        
        Swal.fire({
            title: 'Endorse Reservation?',
            text: 'This will forward the reservation for approval.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, endorse it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('laboratory.reservations.endorse', '_id_') }}".replace('_id_', reservationId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        full_id: fullReservationId // Send the full ID as well
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            currentStatus = 'Approved';
                            $('.status-toggle').removeClass('active');
                            $('.status-toggle[data-status="Approved"]').addClass('active');
                            loadReservations();
                            loadCounts();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'An error occurred while processing your request.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    // Add this event handler for cancel button
    $(document).on('click', '.cancel-reservation', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Cancel Reservation?',
            text: "Are you sure you want to cancel this reservation?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('laboratory.reservations.destroy', '_id_') }}".replace('_id_', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Reservation has been cancelled.'
                        }).then(() => {
                            loadReservations();
                            loadCounts();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.'
                        });
                    }
                });
            }
        });
    });
    // Render pagination
    function renderPagination(meta) {
        let html = '';
        
        if (meta.last_page > 1) {
            html = `
                <li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${meta.current_page - 1}">Previous</a>
                </li>
            `;
            
            for (let i = 1; i <= meta.last_page; i++) {
                html += `
                    <li class="page-item ${meta.current_page === i ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }
            
            html += `
                <li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${meta.current_page + 1}">Next</a>
                </li>
            `;
        }
        
        $('#pagination').html(html);
        
        // Pagination click handler
        $('.page-link').click(function(e) {
            e.preventDefault();
            currentPage = $(this).data('page');
            loadReservations();
        });
    }

    // Update showing entries text
    function updateShowingEntries(meta) {
        const start = (meta.current_page - 1) * meta.per_page + 1;
        const end = Math.min(start + meta.per_page - 1, meta.total);
        $('#recordsShowing').text(`${start} to ${end}`);
        $('#totalRecords').text(meta.total);
    }

    // View reservation
    $(document).on('click', '.view-reservation', function() {
        const id = $(this).data('id');
        $.get("{{ route('laboratory.reservations.show', '_id_') }}".replace('_id_', id), function(data) {
            $('#viewModal .modal-body').html(data);
            $('#viewModal').modal('show');
        });
    });

    // Approve reservation
    $(document).on('click', '.approve-reservation', function() {
        const fullReservationId = $(this).data('id');
        console.log('Full Reservation ID:', fullReservationId);
        
        // Extract just the numeric part if it's a RES-prefixed ID
        const reservationId = fullReservationId.replace('RES', '');
        console.log('Processed Reservation ID:', reservationId);
        
        // Get the current status from the active toggle button
        const currentStatus = $('.status-toggle.active').data('status');
        
        Swal.fire({
            title: 'Approve Reservation?',
            text: currentStatus === 'Approval of Teacher' ? 
                  'This will forward the reservation to SRA/SRS for final approval.' :
                  'This will approve the reservation.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('laboratory.reservations.approve', '_id_') }}".replace('_id_', reservationId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        full_id: fullReservationId,
                        current_status: currentStatus
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            // If teacher approved, switch to SRA/SRS view
                            if (currentStatus === 'Approval of Teacher') {
                                // Update the current status variable
                                currentStatus = 'Approval of SRA / SRS';
                                // Update the active toggle button
                                $('.status-toggle').removeClass('active');
                                $('.status-toggle[data-status="Approval of SRA / SRS"]').addClass('active');
                            } else {
                                // For SRA/SRS approval, switch to Approved view
                                currentStatus = 'Approved';
                                $('.status-toggle').removeClass('active');
                                $('.status-toggle[data-status="Approved"]').addClass('active');
                            }
                            // Reload the reservations and counts
                            loadReservations();
                            loadCounts();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'An error occurred while processing your request.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    // Disapprove reservation
    $(document).on('click', '.disapprove-reservation', function() {
        const fullReservationId = $(this).data('id');
        console.log('Full Reservation ID:', fullReservationId);
        
        // Extract just the numeric part if it's a RES-prefixed ID
        const reservationId = fullReservationId.replace('RES', '');
        console.log('Processed Reservation ID:', reservationId);
        
        // Get the current status from the active toggle button
        const currentStatus = $('.status-toggle.active').data('status');
        
        Swal.fire({
            title: 'Disapprove Reservation?',
            text: 'Please provide a reason for disapproval:',
            input: 'textarea',
            inputPlaceholder: 'Enter detailed reason for disapproval...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Disapprove',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to provide a reason for disapproval!';
                }
                if (value.length < 10) {
                    return 'Please provide a more detailed reason (at least 10 characters)';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                $.ajax({
                    url: "{{ route('laboratory.reservations.disapprove', '_id_') }}".replace('_id_', reservationId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: result.value,
                        full_id: fullReservationId,
                        current_status: currentStatus
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            currentStatus = 'Disapproved';
                            $('.status-toggle').removeClass('active');
                            $('.status-toggle[data-status="Disapproved"]').addClass('active');
                            loadReservations();
                            loadCounts();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'An error occurred while processing your request.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    // Helper functions
    function formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatTime(time) {
        return new Date('2000-01-01 ' + time).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Add these new event handlers for restore and delete actions
    $(document).on('click', '.restore-reservation', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Restore Reservation?',
            text: "This will restore the disapproved reservation to 'For Approval' status.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('laboratory.reservations.restore', '_id_') }}".replace('_id_', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Reservation has been restored.'
                        }).then(() => {
                            loadReservations();
                            loadCounts();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.delete-reservation', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Delete Reservation?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a form dynamically
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('laboratory.reservations.destroy', '_id_') }}".replace('_id_', id);
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Add method spoofing
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                // Add form to document and submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Add debug logging for user role at startup
    console.log('Initial user role:', userRole);
    console.log('Is SRA/SRS:', userRole === 'SRA' || userRole === 'SRS');
});
</script>
@endpush 