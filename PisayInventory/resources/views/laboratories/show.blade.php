@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laboratory Details</h1>
        <div>
            <a href="{{ route('laboratories.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Laboratory Information</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Laboratory ID</th>
                                <td>{{ $laboratory->laboratory_id }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td>{{ $laboratory->laboratory_name }}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{{ $laboratory->location }}</td>
                            </tr>
                            <tr>
                                <th>Capacity</th>
                                <td>{{ $laboratory->capacity }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Occupied' ? 'warning' : 'danger') }}">
                                        {{ $laboratory->status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $laboratory->description ?: 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Equipment List</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="equipmentTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Equipment ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laboratory->equipment as $equipment)
                                <tr>
                                    <td>{{ $equipment->equipment_id }}</td>
                                    <td>{{ $equipment->equipment_name }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $equipment->status === 'Available' ? 'success' : ($equipment->status === 'In Use' ? 'warning' : 'danger') }}">
                                            {{ $equipment->status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No equipment found in this laboratory.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Reservations</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="reservationsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laboratory->reservations->take(5) as $reservation)
                                <tr>
                                    <td>{{ $reservation->reservation_date }}</td>
                                    <td>{{ $reservation->start_time }} - {{ $reservation->end_time }}</td>
                                    <td>{{ Str::limit($reservation->purpose, 30) }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $reservation->status === 'Active' ? 'success' : 'secondary' }}">
                                            {{ $reservation->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($userPermissions->CanView)
                                        <a href="{{ route('laboratory.reservations.show', $reservation->reservation_id) }}" 
                                           class="btn btn-info btn-sm"
                                           title="View Details">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No recent reservations found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this modal at the bottom of your show.blade.php file -->
<div class="modal fade" id="reservationDetailsModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="reservationDetailsModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationDetailsModalLabel">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Reservation details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#equipmentTable').DataTable({
            "pageLength": 5,
            "searching": false,
            "lengthChange": false,
            "language": {
                "emptyTable": "No equipment found in this laboratory"
            }
        });

        $('#reservationsTable').DataTable({
            "pageLength": 5,
            "searching": false,
            "lengthChange": false,
            "language": {
                "emptyTable": "No recent reservations found"
            }
        });

        // Add click handler for view buttons
        $(document).on('click', '.btn-info', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            
            // Load reservation details via AJAX
            $.get(url, function(response) {
                $('#reservationDetailsModal .modal-body').html(response);
                $('#reservationDetailsModal').modal('show');
            }).fail(function(xhr) {
                console.error('Error loading reservation details:', xhr);
                alert('Error loading reservation details. Please try again.');
            });
        });
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
@endsection 