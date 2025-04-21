@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laboratory Request and Equipment Accountability Form</h3>
        </div>
        <div class="card-body">
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
                            <input type="text" class="form-control" id="teacher" name="teacher" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="venue" class="form-label">Venue of the Experiment:</label>
                    <input type="text" class="form-control" id="venue" name="venue" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="date" class="form-label">Date/Inclusive Dates:</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="col-md-6">
                        <label for="time" class="form-label">Inclusive Time of Use:</label>
                        <input type="time" class="form-control" id="time" name="time" required>
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
                                <td><input type="text" class="form-control" name="items[]"></td>
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
                            <input type="text" class="form-control" id="received_by" name="received_by">
                        </div>
                        <div class="mb-3">
                            <label for="date_received" class="form-label">Date Received:</label>
                            <input type="date" class="form-control" id="date_received" name="date_received">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="inspected_by" class="form-label">Received and Inspected by:</label>
                            <input type="text" class="form-control" id="inspected_by" name="inspected_by">
                        </div>
                        <div class="mb-3">
                            <label for="date_inspected" class="form-label">Date Inspected:</label>
                            <input type="date" class="form-control" id="date_inspected" name="date_inspected">
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
    // Add new row
    $('#addRow').click(function() {
        var newRow = `
            <tr>
                <td><input type="number" class="form-control" name="quantities[]"></td>
                <td><input type="text" class="form-control" name="items[]"></td>
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
});
</script>
@endpush
@endsection 