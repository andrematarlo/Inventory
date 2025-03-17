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
    

        <!-- Add Endorsement/Approval/Disapproval Information -->
@if($reservation->endorsed_by)
<div class="mb-3">
    <strong>Endorsed by:</strong>
    <div>
        {{ optional($reservation->endorser)->FirstName }} 
        {{ optional($reservation->endorser)->LastName }}
    </div>
    @if($reservation->endorsed_at)
        <small class="text-muted">{{ \Carbon\Carbon::parse($reservation->endorsed_at)->format('M d, Y h:i A') }}</small>
    @endif
</div>
@endif

@if($reservation->status == 'Approved' && $reservation->approved_by)
<div class="mb-3">
    <strong>Approved by:</strong>
    <div>
        {{ optional($reservation->approver)->FirstName }} 
        {{ optional($reservation->approver)->LastName }}
    </div>
    @if($reservation->approved_at)
        <small class="text-muted">{{ \Carbon\Carbon::parse($reservation->approved_at)->format('M d, Y h:i A') }}</small>
    @endif
</div>
@endif

@if($reservation->status === 'Disapproved')
<div class="mb-3">
    <strong>Disapproved by:</strong>
    <div>
        @if($reservation->disapproved_by && $reservation->disapprover)
            {{ $reservation->disapprover->FirstName }} {{ $reservation->disapprover->LastName }}
            @if($reservation->disapproved_at)
                <br>
                <small class="text-muted">{{ \Carbon\Carbon::parse($reservation->disapproved_at)->format('M d, Y h:i A') }}</small>
            @endif
        @else
            <span class="text-muted">Not specified</span>
        @endif
    </div>
</div>

<div class="mb-3">
    <strong>Reason for Disapproval:</strong>
    <div>{{ $reservation->remarks ?? 'No reason provided' }}</div>
</div>
@endif
</div>