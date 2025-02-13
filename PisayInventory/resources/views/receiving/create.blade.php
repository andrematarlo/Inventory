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
        <h1>Create Receiving Record</h1>
        <a href="{{ route('receiving.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if(isset($purchaseOrder))
                <!-- Display specific PO details -->
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
                            <tr>
                                <th>Total Amount:</th>
                                <td>₱{{ number_format($purchaseOrder->getTotalAmount(), 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Display PO Items -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4>Items to Receive</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Description</th>
                                        <th>Ordered Quantity</th>
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $item)
                                    <tr>
                                        <td>{{ $item->item->ItemCode }}</td>
                                        <td>{{ $item->item->Description }}</td>
                                        <td>{{ $item->Quantity }}</td>
                                        <td>{{ $item->item->Unit }}</td>
                                        <td>₱{{ number_format($item->UnitPrice, 2) }}</td>
                                        <td>₱{{ number_format($item->Quantity * $item->UnitPrice, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Total Amount:</th>
                                        <th>₱{{ number_format($purchaseOrder->getTotalAmount(), 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Receiving Form -->
                <form action="{{ route('receiving.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="PurchaseOrderID" value="{{ $purchaseOrder->PurchaseOrderID }}">
                    
                    <div class="mb-3">
                        <label for="DeliveryDate" class="form-label">Delivery Date</label>
                        <input type="date" 
                               class="form-control @error('DeliveryDate') is-invalid @enderror" 
                               id="DeliveryDate" 
                               name="DeliveryDate" 
                               value="{{ old('DeliveryDate', date('Y-m-d')) }}" 
                               required>
                        @error('DeliveryDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('Notes') is-invalid @enderror" 
                                  id="Notes" 
                                  name="Notes" 
                                  rows="3">{{ old('Notes') }}</textarea>
                        @error('Notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Create Receiving Record</button>
                    </div>
                </form>
            @else
                <!-- Display PO selection form -->
                <form action="{{ route('receiving.create') }}" method="GET">
                    <div class="mb-3">
                        <label for="po_id" class="form-label">Select Purchase Order</label>
                        <select class="form-select" id="po_id" name="po_id" required>
                            <option value="">Select a Purchase Order</option>
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
    $('.po-select').select2({
        placeholder: 'Select Purchase Order',
        width: '100%'
    });

    $('.po-select').on('change', function() {
        let poId = $(this).val();
        if (poId) {
            $.get(`/purchases/${poId}`, function(data) {
                let items = '';
                let total = 0;
                
                data.items.forEach(function(item) {
                    items += `
                        <tr>
                            <td>${item.item.ItemName}</td>
                            <td>${item.Quantity}</td>
                            <td>₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                            <td>₱${parseFloat(item.TotalPrice).toFixed(2)}</td>
                        </tr>
                    `;
                    total += parseFloat(item.TotalPrice);
                });

                $('#poItems').html(items);
                $('#poTotal').text(`₱${total.toFixed(2)}`);
                $('#poDetails').show();
            });
        } else {
            $('#poDetails').hide();
        }
    });
});
</script>
@endsection 