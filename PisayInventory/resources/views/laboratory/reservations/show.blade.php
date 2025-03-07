<div class="reservation-details">
    <div class="mb-3">
        <label class="fw-bold">Reservation ID:</label>
        <p>{{ $reservation->reservation_id }}</p>
    </div>
    
    <div class="mb-3">
        <label class="fw-bold">Laboratory:</label>
        <p>{{ $reservation->laboratory->laboratory_name }}</p>
    </div>
    
    <div class="mb-3">
        <label class="fw-bold">Reserved By:</label>
        <p>
            @if($reservation->reserver && $reservation->reserver->employee)
                {{ $reservation->reserver->employee->FirstName }} 
                {{ $reservation->reserver->employee->LastName }}
            @else
                N/A
            @endif
        </p>
    </div>
    
    <div class="mb-3">
        <label class="fw-bold">Date:</label>
        <p>{{ date('M d, Y', strtotime($reservation->reservation_date)) }}</p>
    </div>
    
    <div class="mb-3">
        <label class="fw-bold">Time:</label>
        <p>
            {{ date('h:i A', strtotime($reservation->start_time)) }} - 
            {{ date('h:i A', strtotime($reservation->end_time)) }}
        </p>
    </div>
    
    <div class="mb-3">
        <label class="fw-bold">Purpose:</label>
        <p>{{ $reservation->purpose }}</p>
    </div>
    
    <div class="mb-3">
        <label class="fw-bold">Number of Students:</label>
        <p>{{ $reservation->num_students ?? 'N/A' }}</p>
    </div>
    
    <div class="mb-3">
        <label class="fw-bold">Status:</label>
        <p>
            <span class="badge {{ $reservation->status === 'Active' ? 'bg-success' : 
                              ($reservation->status === 'Pending' ? 'bg-warning' : 
                              ($reservation->status === 'Cancelled' ? 'bg-danger' : 'bg-secondary')) }}">
                {{ $reservation->status }}
            </span>
        </p>
    </div>
    
    @if($reservation->remarks)
    <div class="mb-3">
        <label class="fw-bold">Remarks:</label>
        <p>{{ $reservation->remarks }}</p>
    </div>
    @endif
</div> 