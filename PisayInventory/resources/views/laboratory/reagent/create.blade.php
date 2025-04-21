@extends('layouts.app')

@section('content')
<div class="container-fluid">
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
                            <input type="text" class="form-control" name="teacher_in_charge">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Venue of the Experiment:</label>
                    <input type="text" class="form-control" name="venue">
                </div>

                <div class="form-group">
                    <label>Date/Inclusive Dates:</label>
                    <input type="text" class="form-control" name="inclusive_dates">
                </div>

                <div class="form-group">
                    <label>Inclusive Time of Use:</label>
                    <input type="text" class="form-control" name="inclusive_time">
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
                                        <td><input type="text" class="form-control" name="reagents[]"></td>
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
                            <input type="text" class="form-control" name="received_by">
                        </div>
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="date" class="form-control" name="date_received">
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
                            <input type="text" class="form-control" name="endorsed_by" placeholder="Subject Teacher/Unit Head">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Approved by:</label>
                            <input type="text" class="form-control" name="approved_by" placeholder="SRS/SRA (Releasing Unit)">
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
            var newRow = `
                <tr>
                    <td><input type="number" class="form-control" name="quantities[]"></td>
                    <td><input type="text" class="form-control" name="reagents[]"></td>
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
    });
</script>
@endpush
@endsection 