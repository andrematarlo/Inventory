@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Laboratory Reservation</h3>
                    <div class="card-tools">
                        <a href="{{ route('laboratory.reservations') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('laboratory.reservations.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="laboratory_id">Laboratory <span class="text-danger">*</span></label>
                                    <select name="laboratory_id" id="laboratory_id" class="form-control @error('laboratory_id') is-invalid @enderror" required>
                                        <option value="">Select Laboratory</option>
                                        @foreach($laboratories as $lab)
                                            <option value="{{ $lab->laboratory_id }}" {{ old('laboratory_id') == $lab->laboratory_id ? 'selected' : '' }}>
                                                {{ $lab->laboratory_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('laboratory_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="reservation_date">Reservation Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           name="reservation_date" 
                                           id="reservation_date" 
                                           class="form-control @error('reservation_date') is-invalid @enderror"
                                           value="{{ old('reservation_date') }}"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           required>
                                    @error('reservation_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           name="start_time" 
                                           id="start_time" 
                                           class="form-control @error('start_time') is-invalid @enderror"
                                           value="{{ old('start_time') }}"
                                           required>
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="end_time">End Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           name="end_time" 
                                           id="end_time" 
                                           class="form-control @error('end_time') is-invalid @enderror"
                                           value="{{ old('end_time') }}"
                                           required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="purpose">Purpose <span class="text-danger">*</span></label>
                                    <textarea name="purpose" 
                                              id="purpose" 
                                              class="form-control @error('purpose') is-invalid @enderror"
                                              rows="3"
                                              required>{{ old('purpose') }}</textarea>
                                    @error('purpose')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="num_students">Number of Students</label>
                                    <input type="number" 
                                           name="num_students" 
                                           id="num_students" 
                                           class="form-control @error('num_students') is-invalid @enderror"
                                           value="{{ old('num_students') }}"
                                           min="1">
                                    @error('num_students')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea name="remarks" 
                                              id="remarks" 
                                              class="form-control @error('remarks') is-invalid @enderror"
                                              rows="3">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Reservation
                                </button>
                                <a href="{{ route('laboratory.reservations') }}" class="btn btn-default">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for reservation_date
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    document.getElementById('reservation_date').min = minDate;

    // Validate end time is after start time
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');

    function validateTimes() {
        if (startTime.value && endTime.value) {
            if (endTime.value <= startTime.value) {
                endTime.setCustomValidity('End time must be after start time');
            } else {
                endTime.setCustomValidity('');
            }
        }
    }

    startTime.addEventListener('change', validateTimes);
    endTime.addEventListener('change', validateTimes);
});
</script>
@endpush 