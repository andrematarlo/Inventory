@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laboratory Details</h1>
        <div>
            @if($userPermissions->CanEdit)
            <a href="{{ route('laboratories.edit', $laboratory->laboratory_id) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit Laboratory
            </a>
            @endif
            <a href="{{ route('laboratories.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
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
                                    <span class="badge badge-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Occupied' ? 'warning' : 'danger') }}">
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laboratory->equipment as $equipment)
                                <tr>
                                    <td>{{ $equipment->equipment_id }}</td>
                                    <td>{{ $equipment->equipment_name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $equipment->status === 'Available' ? 'success' : ($equipment->status === 'In Use' ? 'warning' : 'danger') }}">
                                            {{ $equipment->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($userPermissions->CanView)
                                        <a href="{{ route('equipment.show', $equipment->equipment_id) }}" 
                                           class="btn btn-info btn-sm"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No equipment found in this laboratory.</td>
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
                                        <span class="badge badge-{{ $reservation->status === 'Active' ? 'success' : 'secondary' }}">
                                            {{ $reservation->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($userPermissions->CanView)
                                        <a href="{{ route('laboratory.reservations.show', $reservation->reservation_id) }}" 
                                           class="btn btn-info btn-sm"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
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
    });
</script>
@endpush
@endsection 