@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Create New Deposit</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('pos.deposits.store') }}" method="POST" id="depositForm">
                        @csrf
                        <div class="mb-4">
                            <label for="student_id" class="form-label">Student</label>
                            <select class="form-select select2-student" id="student_id" name="student_id" required>
                                <option value="">Search student by ID or name...</option>
                            </select>
                            @error('student_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0.01" 
                                       required 
                                       value="{{ old('amount') }}">
                            </div>
                            @error('amount')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('pos.deposits.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Deposit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.select2-container--bootstrap-5 {
    width: 100% !important;
}

.select2-container--bootstrap-5 .select2-selection {
    min-height: calc(3.5rem + 2px);
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: 0.25rem;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    padding: 0;
    line-height: 2.25;
}

.select2-container--bootstrap-5 .select2-search__field:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for student search
    $('.select2-student').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search student by ID or name...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("pos.search-students") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                return {
                    results: $.map(data.students, function(student) {
                        return {
                            id: student.student_id,
                            text: student.student_id + ' - ' + student.first_name + ' ' + student.last_name
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Format amount input
    $('#amount').on('input', function() {
        if (this.value < 0) this.value = 0;
    });

    // Form validation
    $('#depositForm').on('submit', function(e) {
        if (!$('#student_id').val()) {
            e.preventDefault();
            alert('Please select a student');
            return false;
        }

        if (!$('#amount').val() || parseFloat($('#amount').val()) <= 0) {
            e.preventDefault();
            alert('Please enter a valid amount greater than 0');
            return false;
        }
    });
});
</script>
@endpush 