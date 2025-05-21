@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laboratory Request and Equipment Accountability Form</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
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
            <form action="{{ route('laboratory.accountability.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="control_no" class="form-label">Control No:</label>
                            <input type="text" class="form-control" id="control_no" value="{{ $nextControlNo }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="sy" class="form-label">SY:</label>
                            <input type="text" class="form-control" id="sy" name="school_year" required>
                        </div>
                        <div class="mb-3">
                            <label for="grade_section" class="form-label">Grade Level and Section:</label>
                            <input type="text" class="form-control" id="grade_section" name="grade_section" required>
                        </div>
                        <div class="mb-3">
                            <label for="num_students" class="form-label">Number of Students:</label>
                            <input type="number" class="form-control" id="num_students" name="number_of_students" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject:</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="concurrent_topic" class="form-label">Concurrent Topic:</label>
                            <input type="text" class="form-control" id="concurrent_topic" name="concurrent_topic" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit:</label>
                            <input type="text" class="form-control" id="unit" name="unit" required>
                        </div>
                        <div class="mb-3">
                            <label for="teacher" class="form-label">Teacher-in-Charge:</label>
                            <select class="form-control" id="teacher" name="teacher_in_charge" required>
                                <option value="">-- Select Teacher --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->FirstName }} {{ $teacher->LastName }}">{{ $teacher->FirstName }} {{ $teacher->LastName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="venue" class="form-label">Venue of the Experiment:</label>
                    <input type="text" class="form-control" id="venue" name="venue" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="inclusive_dates" class="form-label">Date/Inclusive Dates:</label>
                        <input type="date" class="form-control" id="inclusive_dates" name="inclusive_dates" required>
                    </div>
                    <div class="col-md-6">
                        <label for="inclusive_time" class="form-label">Inclusive Time of Use:</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="time" class="form-control" id="inclusive_time_start" name="inclusive_time_start" value="08:00" required>
                            <span>to</span>
                            <input type="time" class="form-control" id="inclusive_time_end" name="inclusive_time_end" value="17:00" required>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4 mb-3">Materials/Equipment Needed</h4>
                <div class="table-responsive">
                    <table class="table table-bordered" id="materialsTable">
                        <thead>
                            <tr>
                                <th>Quantity</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Issued Condition/Remarks</th>
                                <th>Returned Condition/Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="number" class="form-control" name="quantities[]"></td>
                                <td>
                                    <select name="items[]" class="form-control" required>
                                        <option value="">-- Select Item --</option>
                                        @foreach($accountabilityItems as $item)
                                            <option value="{{ $item->item }}">{{ $item->item }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" class="form-control" name="descriptions[]"></td>
                                <td><input type="text" class="form-control" name="issued_conditions[]"></td>
                                <td><input type="text" class="form-control" name="returned_conditions[]"></td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary" id="addRow">Add Row</button>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_by" class="form-label">Received by:</label>
                            @if(count($srsChemUsers) === 1)
                                <input type="text" class="form-control" id="received_by" name="received_by" value="{{ $srsChemUsers[0]->FirstName }} {{ $srsChemUsers[0]->LastName }}" readonly required>
                            @else
                                <select class="form-control" id="received_by" name="received_by" required>
                                    <option value="">-- Select SRS-CHEM --</option>
                                    @foreach($srsChemUsers as $srs)
                                        <option value="{{ $srs->FirstName }} {{ $srs->LastName }}">{{ $srs->FirstName }} {{ $srs->LastName }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="date_received" class="form-label">Date Received:</label>
                            <input type="date" class="form-control" id="date_received" name="date_received">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_and_inspected_by" class="form-label">Received and Inspected by:</label>
                            @if(count($srsChemUsers) === 1)
                                <input type="text" class="form-control" id="received_and_inspected_by" name="received_and_inspected_by" value="{{ $srsChemUsers[0]->FirstName }} {{ $srsChemUsers[0]->LastName }}" readonly required>
                            @else
                                <select class="form-control" id="received_and_inspected_by" name="received_and_inspected_by" required>
                                    <option value="">-- Select SRS-CHEM --</option>
                                    @foreach($srsChemUsers as $srs)
                                        <option value="{{ $srs->FirstName }} {{ $srs->LastName }}">{{ $srs->FirstName }} {{ $srs->LastName }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="date_inspected" class="form-label">Date Inspected:</label>
                            <input type="date" class="form-control" id="date_inspected" name="date_inspected">
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="endorsed_by" class="form-label">Endorsed by:</label>
                            <div class="d-flex flex-column">
                                <input type="text" class="form-control" name="endorsed_by" id="endorsed_by" 
                                    readonly 
                                    placeholder="{{ Auth::user()->role === 'Teacher' ? 'Will be endorsed by Unit Head' : 'Will be endorsed by Subject Teacher' }}">
                                <small class="text-muted text-center mt-1">
                                    {{ Auth::user()->role === 'Teacher' ? 'Unit Head' : 'Subject Teacher' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="approved_by" class="form-label">Approved by:</label>
                            @if(count($srsChemUsers) === 1)
                                <input type="text" class="form-control" name="approved_by" value="{{ $srsChemUsers[0]->FirstName }} {{ $srsChemUsers[0]->LastName }}" readonly required>
                            @else
                                <select class="form-control" name="approved_by" required>
                                    <option value="">-- Select SRS-CHEM --</option>
                                    @foreach($srsChemUsers as $srs)
                                        <option value="{{ $srs->FirstName }} {{ $srs->LastName }}">{{ $srs->FirstName }} {{ $srs->LastName }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Date picker for inclusive_dates
    $('.datepicker').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'YYYY-MM-DD' }
    });

    // Time picker for inclusive_time
    $('.timepicker').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
        timePicker24Hour: true,
        timePickerIncrement: 15,
        locale: { format: 'HH:mm' }
    }, function(start, end, label) {
        // Only set the start time
        $('.timepicker').val(start.format('HH:mm'));
    });
    $('.timepicker').on('show.daterangepicker', function(ev, picker) {
        picker.container.find('.calendar-table').hide();
    });

    // Add new row
    $('#addRow').click(function() {
        var itemSelect = `<select name=\"items[]\" class=\"form-control\" required>\n<option value=\"\">-- Select Item --</option>\n@foreach($accountabilityItems as $item)\n<option value=\"{{ $item->item }}\">{{ $item->item }}</option>\n@endforeach\n</select>`;
        var newRow = `
            <tr>
                <td><input type="number" class="form-control" name="quantities[]"></td>
                <td>${itemSelect}</td>
                <td><input type="text" class="form-control" name="descriptions[]"></td>
                <td><input type="text" class="form-control" name="issued_conditions[]"></td>
                <td><input type="text" class="form-control" name="returned_conditions[]"></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                </td>
            </tr>
        `;
        $('#materialsTable tbody').append(newRow);
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });

    // Prepare item-description mapping from PHP
    const itemDescriptions = @json($itemDescriptions);

    // Delegate event for all item selects (including dynamically added)
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches('select[name="items[]"]')) {
            const selectedItem = e.target.value;
            // Find the description input in the same row
            const row = e.target.closest('tr');
            if (row) {
                const descField = row.querySelector('input[name="descriptions[]"]');
                if (descField) {
                    descField.value = itemDescriptions[selectedItem] || '';
                }
            }
        }
    });

    // Function to update endorsed_by field
    function updateEndorsedBy() {
        const userRole = '{{ Auth::user()->role }}';
        const teacherSelect = $('select[name="teacher_in_charge"]');
        const endorsedByInput = $('#endorsed_by');
        
        if (userRole === 'Teacher') {
            // If user is a teacher, get the Unit Head
            $.ajax({
                url: '{{ route("get.unit.head") }}',
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        endorsedByInput.val(response.data.FirstName + ' ' + response.data.LastName);
                    }
                }
            });
        } else {
            // If user is not a teacher, get the selected teacher's name
            const selectedTeacher = teacherSelect.val();
            if (selectedTeacher) {
                endorsedByInput.val(selectedTeacher);
            }
        }
    }
    // Update endorsed_by when teacher is selected
    $('select[name="teacher_in_charge"]').on('change', updateEndorsedBy);
    // Initial update
    updateEndorsedBy();
});
</script>
@endpush
@endsection 