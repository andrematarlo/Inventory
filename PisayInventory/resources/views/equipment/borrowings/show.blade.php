@php
    use Carbon\Carbon;
@endphp

<div class="container-fluid p-3">
    <div class="row">
        <div class="col-12">
            <table class="table table-bordered">
                <tr>
                    <th width="200">Borrowing ID</th>
                    <td>{{ $borrowing->borrowing_id }}</td>
                </tr>
                <tr>
                    <th>Status</th>
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
                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Equipment</th>
                    <td>
                        {{ $borrowing->equipment->equipment_name ?? 'N/A' }}
                        @if($borrowing->equipment)
                            <br>
                            <small class="text-muted">
                                Serial: {{ $borrowing->equipment->serial_number ?? 'N/A' }}
                                <br>
                                Model: {{ $borrowing->equipment->model_number ?? 'N/A' }}
                            </small>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Borrower</th>
                    <td>
                        @if($borrowing->borrower && $borrowing->borrower->employee)
                            {{ $borrowing->borrower->employee->FirstName }}
                            {{ $borrowing->borrower->employee->LastName }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Borrow Date</th>
                    <td>{{ Carbon::parse($borrowing->borrow_date)->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <th>Expected Return Date</th>
                    <td>{{ Carbon::parse($borrowing->expected_return_date)->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <th>Actual Return Date</th>
                    <td>{{ $borrowing->actual_return_date ? Carbon::parse($borrowing->actual_return_date)->format('M d, Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Condition on Borrow</th>
                    <td>{{ $borrowing->condition_on_borrow }}</td>
                </tr>
                @if($borrowing->actual_return_date)
                    <tr>
                        <th>Condition on Return</th>
                        <td>{{ $borrowing->condition_on_return ?? 'Not specified' }}</td>
                    </tr>
                @endif
                <tr>
                    <th>Purpose</th>
                    <td>{{ $borrowing->purpose }}</td>
                </tr>
                <tr>
                    <th>Remarks</th>
                    <td>{{ $borrowing->remarks ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>
                        @if($borrowing->creator && $borrowing->creator->employee)
                            {{ $borrowing->creator->employee->FirstName }}
                            {{ $borrowing->creator->employee->LastName }}
                            <br>
                            <small class="text-muted">
                                {{ Carbon::parse($borrowing->created_at)->format('M d, Y h:i A') }}
                            </small>
                        @else
                            System
                        @endif
                    </td>
                </tr>
                @if($borrowing->updated_by)
                <tr>
                    <th>Last Modified By</th>
                    <td>
                        @if($borrowing->modifier && $borrowing->modifier->employee)
                            {{ $borrowing->modifier->employee->FirstName }}
                            {{ $borrowing->modifier->employee->LastName }}
                            <br>
                            <small class="text-muted">
                                {{ Carbon::parse($borrowing->updated_at)->format('M d, Y h:i A') }}
                            </small>
                        @else
                            System
                        @endif
                    </td>
                </tr>
                @endif
                @if($borrowing->deleted_at)
                <tr>
                    <th>Deleted By</th>
                    <td>
                        @if($borrowing->deleter && $borrowing->deleter->employee)
                            {{ $borrowing->deleter->employee->FirstName }}
                            {{ $borrowing->deleter->employee->LastName }}
                            <br>
                            <small class="text-muted">
                                {{ Carbon::parse($borrowing->deleted_at)->format('M d, Y h:i A') }}
                            </small>
                        @else
                            System
                        @endif
                    </td>
                </tr>
                @endif
                @if($borrowing->restored_at)
                <tr>
                    <th>Restored By</th>
                    <td>
                        @if($borrowing->restorer && $borrowing->restorer->employee)
                            {{ $borrowing->restorer->employee->FirstName }}
                            {{ $borrowing->restorer->employee->LastName }}
                            <br>
                            <small class="text-muted">
                                {{ Carbon::parse($borrowing->restored_at)->format('M d, Y h:i A') }}
                            </small>
                        @else
                            System
                        @endif
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
</div> 