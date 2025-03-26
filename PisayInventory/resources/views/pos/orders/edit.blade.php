@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Edit Order #{{ $order->OrderNumber }}</h1>
                        <p class="mb-0 opacity-75">Modify order details</p>
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
                    <form id="orderForm" action="{{ route('pos.orders.update', $order->OrderID) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="OrderNumber" class="form-label">Order Number</label>
                            <input type="text" class="form-control" id="OrderNumber" value="{{ $order->OrderNumber }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="Status" class="form-label">Status</label>
                            <select class="form-select @error('Status') is-invalid @enderror" id="Status" name="Status">
                                <option value="pending" {{ $order->Status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ $order->Status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->Status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('Status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="PaymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select @error('PaymentMethod') is-invalid @enderror" id="PaymentMethod" name="PaymentMethod">
                                <option value="cash" {{ $order->PaymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="deposit" {{ $order->PaymentMethod === 'deposit' ? 'selected' : '' }}>Deposit</option>
                            </select>
                            @error('PaymentMethod')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="AmountTendered" class="form-label">Amount Tendered</label>
                            <input type="number" 
                                   class="form-control @error('AmountTendered') is-invalid @enderror" 
                                   id="AmountTendered" 
                                   name="AmountTendered" 
                                   value="{{ old('AmountTendered', $order->AmountTendered) }}"
                                   step="0.01">
                            @error('AmountTendered')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="Remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('Remarks') is-invalid @enderror" 
                                      id="Remarks" 
                                      name="Remarks" 
                                      rows="3">{{ old('Remarks', $order->Remarks) }}</textarea>
                            @error('Remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
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
                                    <td>{{ $item->ItemName }}</td>
                                    <td>₱{{ number_format($item->UnitPrice, 2) }}</td>
                                    <td>{{ $item->Quantity }}</td>
                                    <td class="text-end">₱{{ number_format($item->Subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                    <td class="text-end"><strong>₱{{ number_format($order->TotalAmount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <button type="submit" form="orderForm" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('pos.orders.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle form submission
    $('#orderForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Order updated successfully'
                    }).then(() => {
                        window.location.href = "{{ route('pos.orders.index') }}";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error updating order'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error updating order'
                });
            }
        });
    });
});
</script>
@endpush 