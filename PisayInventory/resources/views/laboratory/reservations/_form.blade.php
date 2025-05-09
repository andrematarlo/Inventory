@php $readonly = $readonly ?? false; @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button type="button" class="btn btn-secondary btn-sm" onclick="printReservationForm()" title="Print Reservation">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
<form>
    @if($readonly)
    <div id="printableReservation">
        <div class="text-center mb-2">
            <h4 class="fw-bold mb-1">PHILIPPINE SCIENCE HIGH SCHOOL SYSTEM</h4>
            <h5 class="mb-2">LABORATORY RESERVATION FORM</h5>
        </div>
        <div class="row mb-3 justify-content-center">
            <div class="col-md-4 d-flex align-items-center justify-content-center">
                <label class="form-label mb-0 me-2">CAMPUS:</label>
                <input type="text" class="form-control form-control-sm w-auto" value="{{ $reservation->campus }}" readonly>
            </div>
        </div>
        <div class="d-flex justify-content-end mb-3">
            <div class="me-3" style="min-width: 250px;">
                <label class="form-label mb-0">Control No.:</label>
                <input type="text" class="form-control form-control-sm" value="{{ $reservation->control_no }}" readonly>
            </div>
            <div style="min-width: 180px;">
                <label class="form-label mb-0">SY:</label>
                <input type="text" class="form-control form-control-sm" value="{{ $reservation->school_year }}" readonly>
            </div>
        </div>
    @endif
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Grade Level and Section:</label>
            <input type="text" class="form-control" name="grade_section" value="{{ $reservation->grade_section }}" {{ $readonly ? 'readonly' : '' }}>
        </div>
        <div class="col-md-6">
            <label class="form-label">Number of Students:</label>
            <input type="number" class="form-control" name="num_students" value="{{ $reservation->num_students }}" {{ $readonly ? 'readonly' : '' }}>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Subject:</label>
            <input type="text" class="form-control" name="subject" value="{{ $reservation->subject }}" {{ $readonly ? 'readonly' : '' }}>
        </div>
        <div class="col-md-6">
            <label class="form-label">Teacher In-Charge:</label>
            <input type="text" class="form-control" name="teacher" value="{{ optional($reservation->teacher)->FirstName }} {{ optional($reservation->teacher)->LastName }}" {{ $readonly ? 'readonly' : '' }}>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Date/Inclusive Dates:</label>
            <div class="d-flex gap-2">
                <input type="date" class="form-control" name="reservation_date_from" value="{{ $reservation->reservation_date_from }}" {{ $readonly ? 'readonly' : '' }}>
                <span class="align-self-center">to</span>
                <input type="date" class="form-control" name="reservation_date_to" value="{{ $reservation->reservation_date_to }}" {{ $readonly ? 'readonly' : '' }}>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Inclusive Time of Use:</label>
            <div class="d-flex gap-2">
                <input type="time" class="form-control" name="start_time" value="{{ $reservation->start_time }}" {{ $readonly ? 'readonly' : '' }}>
                <span class="align-self-center">to</span>
                <input type="time" class="form-control" name="end_time" value="{{ $reservation->end_time }}" {{ $readonly ? 'readonly' : '' }}>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Preferred Lab Room:</label>
        <input type="text" class="form-control" name="laboratory" value="{{ optional($reservation->laboratory)->laboratory_name }}" {{ $readonly ? 'readonly' : '' }}>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Requested by:</label>
            <input type="text" class="form-control" name="requested_by" value="{{ $reservation->requested_by }}" {{ $readonly ? 'readonly' : '' }}>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date Requested:</label>
            <input type="date" class="form-control" name="date_requested" value="{{ $reservation->date_requested ? date('Y-m-d', strtotime($reservation->date_requested)) : '' }}" {{ $readonly ? 'readonly' : '' }}>
        </div>
    </div>
    <div class="mb-4">
        <label class="form-label">If user of the lab is a group, list down the names of students:</label>
        <div id="group_members">
            @foreach(($reservation->group_members ?? []) as $i => $member)
                <div class="input-group mb-2">
                    <span class="input-group-text">{{ $i+1 }}.</span>
                    <input type="text" class="form-control" name="group_members[]" value="{{ $member }}" {{ $readonly ? 'readonly' : '' }}>
                </div>
            @endforeach
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Endorsed by:</label>
            <input type="text" class="form-control" name="endorsed_by" value="{{ optional($reservation->teacher)->FirstName }} {{ optional($reservation->teacher)->LastName }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Approved by:</label>
            <input type="text" class="form-control" name="approved_by" value="{{ optional($reservation->approver)->FirstName }} {{ optional($reservation->approver)->LastName }}" readonly>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Status:</label>
        <input type="text" class="form-control" name="status" value="{{ $reservation->status }}" readonly>
    </div>
</form>
@if($readonly)
<div class="text-start mt-4">
    <small class="text-muted">{{ $form_number ?? 'PSHS-00-F-CID-05-Ver02-Rev1-10/18/20' }}</small>
</div>
</div>
@endif
<script>
function printReservationForm() {
    var printContents = document.getElementById('printableReservation').outerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script> 