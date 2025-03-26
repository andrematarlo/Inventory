@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Order Details #{{ $order->OrderID }}</h1>
                        <p class="mb-0 opacity-75">View order information and items</p>
                    </div>
                    <div>
                        <a href="{{ route('pos.orders.index') }}" class="btn btn-light">
                            <i class="bi bi-arrow-left me-1"></i> Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Order Date</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($order->OrderDate)->format('M d, Y h:ia') }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $order->Status === 'completed' ? 'success' : 
                                ($order->Status === 'pending' ? 'warning' : 
                                ($order->Status === 'cancelled' ? 'danger' : 'info')) }}">
                                {{ ucfirst($order->Status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Customer</dt>
                        <dd class="col-sm-8">
                            @if($order->student_id)
                                {{ $order->customer->FullName ?? 'N/A' }}
                                <br>
                                <small class="text-muted">ID: {{ $order->student_id }}</small>
                            @else
                                <span class="text-muted">Walk-in Customer</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Total Items</dt>
                        <dd class="col-sm-8">{{ $order->items->count() }}</dd>

                        <dt class="col-sm-4">Total Amount</dt>
                        <dd class="col-sm-8">₱{{ number_format($order->TotalAmount, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->ItemName }}</strong>
                                        @if($item->IsCustomItem)
                                        <br>
                                        <small class="text-muted">(Custom Item)</small>
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($item->UnitPrice, 2) }}</td>
                                    <td>{{ $item->Quantity }}</td>
                                    <td class="text-end">₱{{ number_format($item->Subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>₱{{ number_format($order->TotalAmount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 