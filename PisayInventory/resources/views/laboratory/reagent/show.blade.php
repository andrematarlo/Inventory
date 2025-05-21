@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Reagent Request Details</h3>
            <div>
                <a href="{{ route('laboratory.reagent.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                @if($reagentRequest->status === 'pending')
                    <button type="button" class="btn btn-success approve-btn" data-id="{{ $reagentRequest->request_id }}">
                        <i class="bi bi-check-lg"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger reject-btn" data-id="{{ $reagentRequest->request_id }}">
                        <i class="bi bi-x-lg"></i> Reject
                    </button>
                @endif
                @if($reagentRequest->status !== 'deleted')
                    <button type="button" class="btn btn-danger delete-btn" data-id="{{ $reagentRequest->request_id }}">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Control No:</strong></label>
                        <p>{{ $reagentRequest->control_no }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>School Year:</strong></label>
                        <p>{{ $reagentRequest->school_year }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Grade Level and Section:</strong></label>
                        <p>{{ $reagentRequest->grade_section }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Number of Students:</strong></label>
                        <p>{{ $reagentRequest->number_of_students }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Subject:</strong></label>
                        <p>{{ $reagentRequest->subject }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Concurrent Topic:</strong></label>
                        <p>{{ $reagentRequest->concurrent_topic }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Unit:</strong></label>
                        <p>{{ $reagentRequest->unit }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Teacher-in-Charge:</strong></label>
                        <p>{{ $reagentRequest->teacher_in_charge }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Venue:</strong></label>
                        <p>{{ $reagentRequest->venue }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Inclusive Dates:</strong></label>
                        <p>{{ $reagentRequest->inclusive_dates }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Inclusive Time:</strong></label>
                        <p>{{ $reagentRequest->inclusive_time_start }} - {{ $reagentRequest->inclusive_time_end }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Status:</strong></label>
                        <p>
                            <span class="badge bg-{{ $reagentRequest->status === 'approved' ? 'success' : ($reagentRequest->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($reagentRequest->status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <label><strong>Student Names (if group):</strong></label>
                <p style="white-space: pre-line">{{ $reagentRequest->student_names }}</p>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4>Reagent Items</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Quantity</th>
                                    <th>Reagent</th>
                                    <th>SDS</th>
                                    <th>Issued Amount/Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->reagent }}</td>
                                    <td>
                                        @if($item->sds_checked)
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        @else
                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                        @endif
                                    </td>
                                    <td>{{ $item->issued_amount ?? $item->remarks }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Received by:</strong></label>
                        <p>{{ $reagentRequest->received_by }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Date Received:</strong></label>
                        <p>{{ $reagentRequest->date_received->format('F d, Y') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Endorsed by:</strong></label>
                        <p>{{ $reagentRequest->endorsed_by }}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Approved by:</strong></label>
                        <p>{{ $reagentRequest->approved_by }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Approve button click
    $('.approve-btn').click(function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to approve this request?')) {
            $.post(`{{ url('inventory/laboratory/reagent') }}/${id}/approve`, {
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
            $.post(`{{ url('inventory/laboratory/reagent') }}/${id}/reject`, {
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
            $.post(`{{ url('inventory/laboratory/reagent') }}/${id}/delete`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function() {
                window.location.href = '{{ route("laboratory.reagent.index") }}';
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