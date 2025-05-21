@extends('layouts.app')

@section('content')
<div class="container-fluid">
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reagent Request Form</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('laboratory.reagent.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Control No:</label>
                            <input type="text" class="form-control" value="{{ $nextControlNo }}" disabled>
                        </div>
                        <div class="form-group">
                            <label>SY:</label>
                            <input type="text" class="form-control" name="school_year" required>
                        </div>
                        <div class="form-group">
                            <label>Grade Level and Section:</label>
                            <input type="text" class="form-control" name="grade_section" required>
                        </div>
                        <div class="form-group">
                            <label>Number of Students:</label>
                            <input type="number" class="form-control" name="number_of_students" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Subject:</label>
                            <input type="text" class="form-control" name="subject">
                        </div>
                        <div class="form-group">
                            <label>Concurrent Topic:</label>
                            <input type="text" class="form-control" name="concurrent_topic">
                        </div>
                        <div class="form-group">
                            <label>Unit:</label>
                            <input type="text" class="form-control" name="unit">
                        </div>
                        <div class="form-group">
                            <label>Teacher-in-Charge:</label>
                            <select name="teacher_in_charge" class="form-control" required>
                                <option value="">-- Select Teacher --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->FirstName }} {{ $teacher->LastName }}">{{ $teacher->FirstName }} {{ $teacher->LastName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Venue of the Experiment:</label>
                    <input type="text" class="form-control" name="venue">
                </div>

                @php
                    use Carbon\Carbon;
                    $today = Carbon::now();
                    $tomorrow = Carbon::now()->addDay();
                    $nextWeek = Carbon::now()->addWeek();
                    $nextMonth = Carbon::now()->addMonth();
                @endphp
                <div class="form-group">
                    <label>Date/Inclusive Dates:</label>
                    <input type="date" class="form-control" name="inclusive_dates" min="{{ now()->format('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label>Inclusive Time of Use:</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="time" class="form-control" name="inclusive_time_start" value="08:00" required>
                        <span>to</span>
                        <input type="time" class="form-control" name="inclusive_time_end" value="17:00" required>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Reagent Needed</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="reagent_table">
                                <thead>
                                    <tr>
                                        <th>Quantity</th>
                                        <th>Reagent</th>
                                        <th>SDS</th>
                                        <th>Issued Amount/Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="number" class="form-control" name="quantities[]"></td>
                                        <td>
                                            <select name="reagents[]" class="form-control" required>
                                                <option value="">-- Select Reagent --</option>
                                                @foreach($reagentItems as $reagent)
                                                    <option value="{{ $reagent->reagent }}">{{ $reagent->reagent }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="sds[]">
                                            </div>
                                        </td>
                                        <td><input type="text" class="form-control" name="remarks[]"></td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-primary" id="add_row">Add Row</button>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Received by:</label>
                            @if(count($srsChemUsers) === 1)
                                <input type="text" class="form-control" name="received_by" value="{{ $srsChemUsers[0]->FirstName }} {{ $srsChemUsers[0]->LastName }}" readonly required>
                            @else
                                <select class="form-control" name="received_by" required>
                                    <option value="">-- Select SRS-CHEM --</option>
                                    @foreach($srsChemUsers as $srs)
                                        <option value="{{ $srs->FirstName }} {{ $srs->LastName }}">{{ $srs->FirstName }} {{ $srs->LastName }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="date" class="form-control" name="date_received" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Student Names (if group):</label>
                    <textarea class="form-control" name="student_names" rows="5" placeholder="1.&#10;2.&#10;3.&#10;4.&#10;5."></textarea>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Endorsed by:</label>
                            <div class="d-flex flex-column">
                                <input type="text" class="form-control" id="endorsed_by_display" 
                                    readonly 
                                    placeholder="{{ Auth::user()->role === 'Teacher' ? 'Will be endorsed by Unit Head' : 'Will be endorsed by Subject Teacher' }}">
                                <input type="hidden" name="endorsed_by" id="endorsed_by">
                                <small class="text-muted text-center mt-1">
                                    {{ Auth::user()->role === 'Teacher' ? 'Unit Head' : 'Subject Teacher' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Approved by:</label>
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

                <div class="alert alert-info mt-3">
                    <ul class="mb-0">
                        <li>Students must certify that he/she/they have read the safety information as specified in the Safety Data Sheet (SDS) of the reagents being requested.</li>
                        <li>This form must be filled out completely and legibly and submitted, together with a suitable container with cover and proper label, to the SRA of the unit which will release the reagents.</li>
                        <li>Requests not in accordance with existing Unit regulations and considerations may not be granted.</li>
                        <li>The reagents will be released to the SRA of the requesting unit.</li>
                    </ul>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Submit Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Add new row
        $('#add_row').click(function() {
            var reagentSelect = `<select name=\"reagents[]\" class=\"form-control\" required>\n<option value=\"\">-- Select Reagent --</option>\n@foreach($reagentItems as $reagent)\n<option value=\"{{ $reagent->reagent }}\">{{ $reagent->reagent }}</option>\n@endforeach\n</select>`;
            var newRow = `
                <tr>
                    <td><input type="number" class="form-control" name="quantities[]"></td>
                    <td>${reagentSelect}</td>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="sds[]">
                        </div>
                    </td>
                    <td><input type="text" class="form-control" name="remarks[]"></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                    </td>
                </tr>
            `;
            $('#reagent_table tbody').append(newRow);
        });

        // Remove row
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Function to update endorsed_by field
        function updateEndorsedBy() {
            const userRole = '{{ Auth::user()->role }}';
            const teacherSelect = $('select[name="teacher_in_charge"]');
            const endorsedByDisplay = $('#endorsed_by_display');
            const endorsedByInput = $('#endorsed_by');
            
            if (userRole === 'Teacher') {
                // If user is a teacher, get the Unit Head
                $.ajax({
                    url: '{{ route("get.unit.head") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            const unitHeadName = response.data.FirstName + ' ' + response.data.LastName;
                            endorsedByDisplay.val(unitHeadName);
                            endorsedByInput.val(unitHeadName);
                        }
                    }
                });
            } else {
                // If user is not a teacher, get the selected teacher's name
                const selectedTeacher = teacherSelect.val();
                if (selectedTeacher) {
                    endorsedByDisplay.val(selectedTeacher);
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