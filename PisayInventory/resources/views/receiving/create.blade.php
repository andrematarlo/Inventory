@extends('layouts.app')

@section('title', 'Create Receiving')

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create Receiving</h1>
        <a href="{{ route('receiving.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <form action="{{ route('receiving.store') }}" method="POST" id="receivingForm">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Purchase Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Purchase Order</label>
                            <select name="PurchaseOrderID" class="form-control po-select" required>
                                <option value="">Select Purchase Order</option>
                                @foreach($pendingPOs as $po)
                                <option value="{{ $po->PurchaseOrderID }}">
                                    {{ $po->PONumber }} - {{ $po->supplier->CompanyName }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="poDetails" style="display: none;">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Ordered Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="poItems">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                            <td id="poTotal"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Receiving Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Date Received</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ date('M d, Y') }}" 
                                   readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="Notes" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Enter any notes here..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Receive Items
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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