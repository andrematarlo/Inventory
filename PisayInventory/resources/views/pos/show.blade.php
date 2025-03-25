@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Order Details: {{ $order->OrderNumber }}</h1>
                        <p class="mb-0 opacity-75">
                            <span class="badge {{ $order->Status == 'completed' ? 'bg-success' : ($order->Status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ ucfirst($order->Status) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('pos.index') }}" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Back to Orders
                        </a>
                        
                        @if($order->Status == 'pending')
                        <a href="{{ route('pos.process.byid', $order->OrderID) }}" class="btn btn-success ms-2">
                            <i class="bi bi-check-circle"></i> Process Order
                        </a>
                        
                        <a href="{{ route('pos.cancel-order', $order->OrderID) }}" 
                           class="btn btn-danger ms-2"
                           onclick="return confirm('Are you sure you want to cancel this order?')">
                            <i class="bi bi-x-circle"></i> Cancel Order
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Order Number:</div>
                        <div class="col-md-7">{{ $order->OrderNumber }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Date Created:</div>
                        <div class="col-md-7">
                            {{ $order->created_at ? $order->created_at->format('M d, Y g:i A') : 'N/A' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Customer:</div>
                        <div class="col-md-7">
                            @if($order->student)
                                {{ $order->student->first_name }} {{ $order->student->last_name }}
                                <small class="text-muted d-block">(Student ID: {{ $order->student->student_id }})</small>
                            @else
                                Walk-in Customer
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Status:</div>
                        <div class="col-md-7">
                            <span class="badge {{ $order->Status == 'completed' ? 'bg-success' : ($order->Status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ ucfirst($order->Status) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Payment Method:</div>
                        <div class="col-md-7">
                            <span class="badge {{ $order->PaymentMethod == 'cash' ? 'bg-success' : 'bg-info' }}">
                                {{ ucfirst($order->PaymentMethod) }}
                            </span>
                        </div>
                    </div>

                    @if($order->ProcessedAt)
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Processed At:</div>
                        <div class="col-md-7">
                            {{ $order->ProcessedAt ? \Carbon\Carbon::parse($order->ProcessedAt)->format('M d, Y g:i A') : 'N/A' }}
                        </div>
                    </div>
                    @endif

                    @if($order->Remarks)
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Remarks:</div>
                        <div class="col-md-7">{{ $order->Remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Item</th>
                                    <th width="80" class="text-center">Quantity</th>
                                    <th width="120" class="text-end">Unit Price</th>
                                    <th width="120" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($order->items && $order->items->count() > 0)
                                    @foreach($order->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->ItemName ?? 'Unknown Item' }}
                                            @if(isset($item->IsCustomItem) && $item->IsCustomItem)
                                                <span class="badge bg-secondary">Custom</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->Quantity ?? 1 }}</td>
                                        <td class="text-end">₱{{ number_format($item->UnitPrice ?? 0, 2) }}</td>
                                        <td class="text-end">₱{{ number_format($item->Subtotal ?? ($item->UnitPrice * ($item->Quantity ?? 1)), 2) }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">No items found for this order</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr>
                                    <th colspan="4" class="text-end">Total Amount:</th>
                                    <th class="text-end">₱{{ number_format($order->TotalAmount ?? 0, 2) }}</th>
                                </tr>
                                @if(isset($order->AmountTendered) && $order->AmountTendered)
                                <tr>
                                    <th colspan="4" class="text-end">Amount Tendered:</th>
                                    <td class="text-end">₱{{ number_format($order->AmountTendered, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Change:</th>
                                    <td class="text-end">₱{{ number_format($order->ChangeAmount ?? 0, 2) }}</td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 