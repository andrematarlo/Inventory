<div class="reservation-details">
    <div class="mb-3">
        <strong>Reservation ID:</strong>
        <div>{{ $reservation->reservation_id }}</div>
    </div>

    <div class="mb-3">
        <strong>Laboratory:</strong>
        <div>{{ $reservation->laboratory->laboratory_name }}</div>
    </div>

    <div class="mb-3">
        <strong>Reserved By:</strong>
        <div>{{ $reservation->requested_by }}</div>
    </div>

    <div class="mb-3">
        <strong>Date:</strong>
        <div>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}</div>
    </div>

    <div class="mb-3">
        <strong>Time:</strong>
        <div>{{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}</div>
    </div>

    <div class="mb-3">
        <strong>Number of Students:</strong>
        <div>{{ $reservation->num_students }}</div>
    </div>

        <!-- Add Group Members section -->
        @if($reservation->group_members && count(array_filter($reservation->group_members)) > 0)
    <div class="mb-3">
        <strong>Group Members:</strong>
        <div>
            <ol class="ps-3 mb-0">
                @foreach($reservation->group_members as $member)
                    @if(!empty($member))
                        <li>{{ $member }}</li>
                    @endif
                @endforeach
            </ol>
        </div>
    </div>
    @endif

    <div class="mb-3">
        <strong>Status:</strong>
        <div>
            @if($reservation->status == 'For Approval')
                <span class="badge bg-warning">For Approval</span>
            @elseif($reservation->status == 'Approved')
                <span class="badge bg-success">Approved</span>
            @elseif($reservation->status == 'Disapproved')
                <span class="badge bg-danger">Disapproved</span>
            @else
                <span class="badge bg-secondary">Cancelled</span>
            @endif
        </div>
    </div>

    @if($reservation->status == 'Disapproved')
    <div class="mb-3">
        <strong>Reason for Disapproval:</strong>
        <div>{{ $reservation->remarks }}</div>
    </div>
    @elseif($reservation->remarks)
    <div class="mb-3">
        <strong>Remarks:</strong>
        <div>{{ $reservation->remarks }}</div>
    </div>
    @endif
</div>