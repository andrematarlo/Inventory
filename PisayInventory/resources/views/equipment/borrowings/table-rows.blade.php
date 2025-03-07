@php
    use Carbon\Carbon;
@endphp

@forelse($borrowings as $borrowing)
    <tr>
        <td>
            <div class="btn-group" role="group">
                @if(!isset($isDeleted))
                    @if($userPermissions->CanView)
                        <button type="button"
                               class="btn btn-sm btn-icon view-borrowing"
                               style="background-color: rgba(13, 202, 240, 0.15); color: #0dcaf0;"
                               title="View"
                               data-id="{{ $borrowing->borrowing_id }}">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    @endif

                    @if($userPermissions->CanEdit && !$borrowing->return_date)
                        <button type="button"
                               class="btn btn-sm btn-icon return-equipment"
                               style="background-color: rgba(40, 167, 69, 0.15); color: #28a745;"
                               title="Return Equipment"
                               data-id="{{ $borrowing->borrowing_id }}">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    @endif

                    @if($userPermissions->CanDelete && !$borrowing->return_date)
                        <button type="button"
                                class="btn btn-sm btn-icon delete-borrowing"
                                style="background-color: rgba(220, 53, 69, 0.15); color: #dc3545;"
                                title="Delete"
                                data-id="{{ $borrowing->borrowing_id }}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    @endif
                @else
                    @if($userPermissions->CanEdit)
                        <button type="button"
                               class="btn btn-sm btn-icon restore-borrowing"
                               style="background-color: rgba(40, 167, 69, 0.15); color: #28a745;"
                               title="Restore"
                               data-id="{{ $borrowing->borrowing_id }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    @endif
                @endif
            </div>
        </td>
        <td>
            @php
                $statusClass = match(true) {
                    $borrowing->actual_return_date !== null => 'bg-success',
                    Carbon::parse($borrowing->expected_return_date)->isPast() && !$borrowing->actual_return_date => 'bg-danger',
                    default => 'bg-warning'
                };
                
                $status = match(true) {
                    $borrowing->actual_return_date !== null => 'Returned',
                    Carbon::parse($borrowing->expected_return_date)->isPast() && !$borrowing->actual_return_date => 'Overdue',
                    default => 'Borrowed'
                };
            @endphp
            <span class="badge {{ $statusClass }}">
                {{ $status }}
            </span>
        </td>
        <td>{{ $borrowing->borrowing_id }}</td>
        <td>{{ $borrowing->equipment->name ?? 'N/A' }}</td>
        <td>
            @if($borrowing->borrower && $borrowing->borrower->employee)
                {{ $borrowing->borrower->employee->FirstName }} 
                {{ $borrowing->borrower->employee->LastName }}
            @else
                N/A
            @endif
        </td>
        <td>{{ \Carbon\Carbon::parse($borrowing->borrow_date)->format('M d, Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($borrowing->expected_return_date)->format('M d, Y') }}</td>
        <td>
            {{ $borrowing->actual_return_date ? \Carbon\Carbon::parse($borrowing->actual_return_date)->format('M d, Y') : '-' }}
        </td>
        <td>{{ Str::limit($borrowing->purpose, 30) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center">No borrowings found.</td>
    </tr>
@endforelse 