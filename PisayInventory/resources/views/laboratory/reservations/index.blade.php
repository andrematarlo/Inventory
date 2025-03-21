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
                <button type="button" class="btn btn-outline-warning status-toggle active" data-status="For Approval">
                    For Approval <span class="badge bg-warning ms-2" id="forApprovalCount">0</span>
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
    const userPermissions = @json($userPermissions);
    console.log('userPermissions:', userPermissions); 
$(document).ready(function() {
    let currentStatus = 'For Approval';
    let currentPage = 1;
    let searchQuery = '';
    let entriesPerPage = 10;

    // Initial load
    loadReservations();
    loadCounts();

    // Status toggle click handler
    $('.status-toggle').click(function() {
        $('.status-toggle').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status');
        currentPage = 1;
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
    $.ajax({
        url: "{{ route('laboratory.reservations.data') }}",
        data: {
            status: currentStatus,
            page: currentPage,
            search: searchQuery,
            per_page: entriesPerPage
        },
        success: function(response) {
            console.log('Response:', response); // Debug log
            renderTable(response.data);
            renderPagination(response.meta);
            updateShowingEntries(response.meta);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}

    // Load status counts
    function loadCounts() {
    $.get("{{ route('laboratory.reservations.counts') }}", function(response) {
        $('#forApprovalCount').text(response.forApproval);
        $('#approvedCount').text(response.approved);
        $('#cancelledCount').text(response.cancelled);
        $('#disapprovedCount').text(response.disapproved);  // Add this line
    });
}

    // Render table
    function renderTable(reservations) {
    let html = '';
    
    if (!reservations || reservations.length === 0) {
        html = '<tr><td colspan="9" class="text-center">No reservations found</td></tr>';
    } else {
        reservations.forEach(function(reservation) {
            const isStudent = '{{ Auth::user()->role }}' === 'Student';
            const isTeacher = '{{ Auth::user()->role }}' === 'Teacher';
            const isOwnReservation = reservation.reserver_id === '{{ Auth::id() }}';
            const currentUserEmployeeId = {{ Auth::user()->employee?->EmployeeID ?? 'null' }};
            const isTeacherInCharge = reservation.teacher_id == currentUserEmployeeId;
            const isAdmin = {{ Auth::user()->role === 'Admin' ? 'true' : 'false' }};
            const userRole = '{{ Auth::user()->role }}';
            const isSRSorSRA = userRole === 'SRS' || userRole === 'SRA';
            
            console.log('Debug Info:', {
                teacherId: reservation.teacher_id,
                currentUserEmployeeId: currentUserEmployeeId,
                isTeacherInCharge: isTeacherInCharge,
                requestedByType: reservation.requested_by_type,
                endorsementStatus: reservation.endorsement_status,
                isAdmin: isAdmin,
                userRole: userRole,
                isSRSorSRA: isSRSorSRA
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

                            ${/* Teacher actions */
                            isTeacher ? `
                                ${reservation.status === 'Disapproved' ? `
                                    <!-- Restore button for disapproved reservations -->
                                    <button type="button" class="btn btn-success btn-sm restore-reservation" 
                                            data-id="${reservation.reservation_id}" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <!-- Delete button for disapproved reservations -->
                                    <button type="button" class="btn btn-danger btn-sm delete-reservation" 
                                            data-id="${reservation.reservation_id}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                ` : `
                                    <!-- Approve button for pending reservations -->
                                    <button type="button" class="btn btn-success btn-sm approve-reservation" 
                                            data-id="${reservation.reservation_id}" title="Approve"
                                            ${reservation.status !== 'For Approval' ? 'disabled' : ''}>
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <!-- Disapprove button for pending reservations -->
                                    <button type="button" class="btn btn-danger btn-sm disapprove-reservation" 
                                            data-id="${reservation.reservation_id}" title="Disapprove"
                                            ${reservation.status !== 'For Approval' ? 'disabled' : ''}>
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                `}
                            ` : ''}

                            ${/* Student actions */
                            isStudent && isOwnReservation ? `
                                <!-- Cancel button - only for own pending reservations -->
                                ${reservation.status === 'For Approval' ? `
                                    <button type="button" class="btn btn-danger btn-sm cancel-reservation" 
                                            data-id="${reservation.reservation_id}" title="Cancel">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                ` : ''}
                            ` : ''}
                        </div>
                    </td>
                    <td>${reservation.control_no}</td>
                    <td>${reservation.laboratory.laboratory_name}</td>
                    <td>${reservation.grade_section}</td>
                    <td>${reservation.subject}</td>
                    <td>${reservation.teacher ? `${reservation.teacher.FirstName} ${reservation.teacher.LastName}` : '-'}</td>
                    <td>${formatDate(reservation.reservation_date)}</td>
                    <td>${formatTime(reservation.start_time)} - ${formatTime(reservation.end_time)}</td>
                    <td>${reservation.requested_by}</td>
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
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Approve Reservation?',
            text: "This will approve the laboratory reservation request.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                approveReservation(id, 'Approved');
            }
        });
    });

   // Disapprove reservation click handler
$(document).on('click', '.disapprove-reservation', function() {
    const id = $(this).data('id');
    
    Swal.fire({
        title: 'Disapprove Reservation?',
        text: "Please provide a reason for disapproval:",
        input: 'textarea',
        inputPlaceholder: 'Enter detailed reason for disapproval...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Disapprove',
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
            disapproveReservation(id, result.value);
        }
    });
});

// Separate function for disapproval
function disapproveReservation(id, remarks) {
                $.ajax({
        url: "{{ route('laboratory.reservations.disapprove', '_id_') }}".replace('_id_', id),
                    type: 'POST',
                    data: {
            _token: '{{ csrf_token() }}',
            remarks: remarks
                    },
                    success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Reservation Disapproved',
                text: 'The reservation has been disapproved with the provided reason.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Update the status toggle buttons
                $('.status-toggle').removeClass('active');
                $('.status-toggle[data-status="Disapproved"]').addClass('active');
                
                // Update current status and reload
                currentStatus = 'Disapproved';
                currentPage = 1; // Reset to first page
                loadReservations();
                loadCounts();
                        });
                    },
                    error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to disapprove the reservation. Please try again.',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}
    // Approve/Disapprove function
    function approveReservation(id, status, remarks = null) {
        $.ajax({
            url: "{{ route('laboratory.reservations.approve', '_id_') }}".replace('_id_', id),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status,
                remarks: remarks
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message
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
                            text: 'Reservation has been deleted.'
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
});
</script>
@endpush 