@extends('layouts.app')

@section('title', 'Create Receiving Record')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>{{ isset($existingReceiving) ? 'Edit Receiving Record' : 'Create Receiving Record' }}</h1>
        <a href="{{ route('receiving.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if(isset($purchaseOrder))
                <!-- Display PO Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Purchase Order Details</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th>PO Number:</th>
                                <td>{{ $purchaseOrder->PONumber }}</td>
                            </tr>
                            <tr>
                                <th>Supplier:</th>
                                <td>{{ $purchaseOrder->supplier->CompanyName }}</td>
                            </tr>
                            <tr>
                                <th>Order Date:</th>
                                <td>{{ $purchaseOrder->OrderDate->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Items Table -->
                <form action="{{ route('receiving.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="PurchaseOrderID" value="{{ $purchaseOrder->PurchaseOrderID }}">
                    @if(isset($existingReceiving))
                        <input type="hidden" name="existingReceiving" value="{{ $existingReceiving->ReceivingID }}">
                    @endif
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4>Items to Receive</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Ordered Quantity</th>
                                        <th>Received Quantity</th>
                                        <th>Quantity to Receive</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $item)
                                    <tr>
                                        <td>{{ $item->item->ItemName }}</td>
                                        <td>{{ $item->Quantity }}</td>
                                        <td>{{ $item->received_qty ?? 0 }}</td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control received-qty" 
                                                   name="items[{{ $item->ItemId }}][quantity]" 
                                                   min="0" 
                                                   max="{{ $item->remaining_qty }}" 
                                                   value="0"
                                                   data-ordered="{{ $item->Quantity }}"
                                                   data-received="{{ $item->received_qty ?? 0 }}">
                                        </td>
                                        <td>₱{{ number_format($item->UnitPrice, 2) }}</td>
                                        <td class="item-total">₱0.00</td>
                                        <td>
                                            @php
                                                $statusString = is_array($item->status) ? 
                                                    (string)($item->status['status'] ?? 'Pending') : 
                                                    (string)($item->status ?? 'Pending');
                                                    
                                                $statusClass = match($statusString) {
                                                    'Complete' => 'bg-success',
                                                    'Partial' => 'bg-warning',
                                                    default => 'bg-warning'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }} item-status">
                                                {{ $statusString }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="DeliveryDate" class="form-label">Delivery Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="DeliveryDate" 
                               name="DeliveryDate" 
                               value="{{ date('Y-m-d') }}" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="Notes" class="form-label">Notes</label>
                        <textarea class="form-control" 
                                  id="Notes" 
                                  name="Notes" 
                                  rows="3"></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($existingReceiving) ? 'Edit Receiving Record' : 'Create Receiving Record' }}
                        </button>
                    </div>
                </form>
            @else
                <!-- PO Selection Form -->
                <form action="{{ route('receiving.create') }}" method="GET">
                    <div class="mb-3">
                        <label for="po_id" class="form-label">Select Purchase Order</label>
                        <select name="po_id" class="form-select" required>
                            <option value="">Select Purchase Order</option>
                            @foreach($pendingPOs as $po)
                            <option value="{{ $po->PurchaseOrderID }}">
                                {{ $po->PONumber }} - {{ $po->supplier->CompanyName }}
                                (₱{{ number_format($po->getTotalAmount(), 2) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize status and totals on page load
    $('.received-qty').each(function() {
        const input = $(this);
        const row = input.closest('tr');
        const previouslyReceived = parseInt(input.data('received')) || 0;
        const orderedQty = parseInt(input.data('ordered'));
        
        // Set initial total
        const unitPrice = parseFloat(row.find('td:eq(4)').text().replace('₱', '').replace(',', ''));
        const total = previouslyReceived * unitPrice;
        row.find('.item-total').text('₱' + total.toFixed(2));

        // Set initial status and remaining quantity
        const statusBadge = row.find('.item-status');
        const currentStatus = statusBadge.text().trim();
        const remainingQty = orderedQty - previouslyReceived;
        
        // Keep existing status if it exists and set max quantity
        if (currentStatus === 'Complete') {
            statusBadge.removeClass('bg-warning').addClass('bg-success');
            input.prop('disabled', true);
            input.val(0);
        } else {
            input.attr('max', remainingQty);
            input.val(0);
            if (previouslyReceived > 0) {
                statusBadge.removeClass('bg-success').addClass('bg-warning').text('Partial');
            } else {
                statusBadge.removeClass('bg-success').addClass('bg-warning').text('Pending');
            }
        }
    });

    function updateItemStatus(input) {
        const row = input.closest('tr');
        const receivedQty = parseInt(input.val()) || 0;
        const orderedQty = parseInt(input.data('ordered'));
        const previouslyReceived = parseInt(input.data('received')) || 0;
        const totalReceived = previouslyReceived + receivedQty;
        
        // Update total
        const unitPrice = parseFloat(row.find('td:eq(4)').text().replace('₱', '').replace(',', ''));
        const total = receivedQty * unitPrice;
        row.find('.item-total').text('₱' + total.toFixed(2));
        
        // Update status badge
        const statusBadge = row.find('.item-status');
        
        if (totalReceived >= orderedQty) {
            statusBadge.removeClass('bg-warning').addClass('bg-success').text('Complete');
        } else if (totalReceived > previouslyReceived) {
            statusBadge.removeClass('bg-success').addClass('bg-warning').text('Partial');
        } else {
            statusBadge.removeClass('bg-success').addClass('bg-warning').text('Pending');
        }
    }

    // Handle quantity input changes
    $('.received-qty').on('input', function() {
        const input = $(this);
        const orderedQty = parseInt(input.data('ordered'));
        const previouslyReceived = parseInt(input.data('received')) || 0;
        const currentQty = parseInt(input.val()) || 0;
        const remainingQty = orderedQty - previouslyReceived;
        
        if (currentQty > remainingQty) {
            Swal.fire({
                title: 'Invalid Quantity',
                text: `Maximum receivable quantity is ${remainingQty}`,
                icon: 'warning'
            });
            input.val(remainingQty);
        } else if (currentQty < 0) {
            input.val(0);
        }
        
        updateItemStatus(input);
    });

    // Form submission
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        let totalReceiving = 0;
        
        $('.received-qty').each(function() {
            const qty = parseInt($(this).val()) || 0;
            totalReceiving += qty;
        });
        
        if (totalReceiving === 0) {
            Swal.fire({
                title: 'No Items to Receive',
                text: 'Please enter quantity for at least one item',
                icon: 'error'
            });
            return false;
        }
        
        if (isValid) {
            this.submit();
        }
    });
});
</script>
@endsection 