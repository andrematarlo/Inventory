@forelse($reservations as $reservation)
    <tr>
        <td>
            <div class="btn-group" role="group">
                @if(!isset($isDeleted))
                    @if($userPermissions->CanView)
                        <button type="button"
                               class="btn btn-sm btn-icon view-reservation"
                               style="background-color: rgba(13, 202, 240, 0.15); color: #0dcaf0;"
                               title="View"
                               data-id="{{ $reservation->reservation_id }}">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    @endif

                    @if($userPermissions->CanEdit && $reservation->isActive() && $reservation->isUpcoming())
                        <button type="button"
                               class="btn btn-sm btn-icon edit-reservation"
                               style="background-color: rgba(255, 193, 7, 0.15); color: #ffc107;"
                               title="Edit"
                               data-id="{{ $reservation->reservation_id }}">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    @endif

                    @if($userPermissions->CanDelete && $reservation->canBeCancelled())
                        <button type="button"
                                class="btn btn-sm btn-icon delete-reservation"
                                style="background-color: rgba(220, 53, 69, 0.15); color: #dc3545;"
                                title="Delete"
                                data-id="{{ $reservation->reservation_id }}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    @endif
                @else
                    @if($userPermissions->CanEdit)
                        <button type="button"
                               class="btn btn-sm btn-icon restore-reservation"
                               style="background-color: rgba(40, 167, 69, 0.15); color: #28a745;"
                               title="Restore"
                               data-id="{{ $reservation->reservation_id }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    @endif
                @endif
            </div>
        </td>
        <td>
            @php
                $statusClass = match($reservation->status) {
                    'Active' => 'bg-success',
                    'Pending' => 'bg-warning',
                    'Cancelled' => 'bg-danger',
                    default => 'bg-secondary'
                };
            @endphp
            <span class="badge {{ $statusClass }}">
                {{ $reservation->status }}
            </span>
        </td>
        <td>{{ $reservation->reservation_id }}</td>
        <td>{{ $reservation->laboratory->laboratory_name ?? 'N/A' }}</td>
        <td>
            @if($reservation->reserver && $reservation->reserver->employee)
                {{ $reservation->reserver->employee->FirstName }} 
                {{ $reservation->reserver->employee->LastName }}
            @else
                N/A
            @endif
        </td>
        <td>{{ date('M d, Y', strtotime($reservation->reservation_date)) }}</td>
        <td>
            {{ date('h:i A', strtotime($reservation->start_time)) }} - 
            {{ date('h:i A', strtotime($reservation->end_time)) }}
        </td>
        <td>{{ Str::limit($reservation->purpose, 30) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center">No reservations found.</td>
    </tr>
@endforelse 