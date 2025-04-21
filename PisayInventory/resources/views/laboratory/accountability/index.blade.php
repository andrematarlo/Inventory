@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Laboratory Equipment Accountability Records</h3>
            <a href="{{ route('laboratory.accountability.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Request
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                </div>
            </div>

            <!-- Records Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Control No.</th>
                            <th>Date</th>
                            <th>Grade & Section</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountabilities as $record)
                        <tr>
                            <td>{{ $record->control_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->created_at)->format('M d, Y') }}</td>
                            <td>{{ $record->grade_section }}</td>
                            <td>{{ $record->subject }}</td>
                            <td>{{ $record->teacher_in_charge }}</td>
                            <td>
                                <span class="badge bg-{{ $record->status === 'pending' ? 'warning' : ($record->status === 'approved' ? 'success' : ($record->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('laboratory.accountability.show', $record->accountability_id) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($record->status === 'pending')
                                    <button type="button" 
                                            class="btn btn-sm btn-success approve-btn" 
                                            data-id="{{ $record->accountability_id }}"
                                            title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger reject-btn" 
                                            data-id="{{ $record->accountability_id }}"
                                            title="Reject">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    @endif
                                    @if($record->status !== 'deleted')
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-btn" 
                                            data-id="{{ $record->accountability_id }}"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @else
                                    <button type="button" 
                                            class="btn btn-sm btn-success restore-btn" 
                                            data-id="{{ $record->accountability_id }}"
                                            title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Status filter
    $('#statusFilter').change(function() {
        filterRecords();
    });

    // Search functionality
    $('#searchInput').keyup(function() {
        filterRecords();
    });

    function filterRecords() {
        var status = $('#statusFilter').val();
        var search = $('#searchInput').val().toLowerCase();

        $('tbody tr').each(function() {
            var showStatus = status === '' || $(this).find('td:eq(5)').text().toLowerCase().includes(status);
            var showSearch = search === '' || $(this).text().toLowerCase().includes(search);
            $(this).toggle(showStatus && showSearch);
        });
    }

    // Approve button click
    $('.approve-btn').click(function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to approve this request?')) {
            $.post(`/laboratory/accountability/${id}/approve`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function() {
                location.reload();
            })
            .fail(function(response) {
                alert('Error: ' + response.responseJSON.message);
            });
        }
    });

    // Reject button click
    $('.reject-btn').click(function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to reject this request?')) {
            $.post(`/laboratory/accountability/${id}/reject`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function() {
                location.reload();
            })
            .fail(function(response) {
                alert('Error: ' + response.responseJSON.message);
            });
        }
    });

    // Delete button click
    $('.delete-btn').click(function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this record? This cannot be undone.')) {
            $.post(`/laboratory/accountability/${id}/delete`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function() {
                location.reload();
            })
            .fail(function(response) {
                alert('Error: ' + response.responseJSON.message);
            });
        }
    });

    // Restore button click
    $('.restore-btn').click(function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to restore this record?')) {
            $.post(`/laboratory/accountability/${id}/restore`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function() {
                location.reload();
            })
            .fail(function(response) {
                alert('Error: ' + response.responseJSON.message);
            });
        }
    });
});
</script>
@endpush
@endsection 