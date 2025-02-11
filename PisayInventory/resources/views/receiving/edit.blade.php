@extends('layouts.app')

@section('title', 'Edit Receiving')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Receiving</h1>
        <a href="{{ route('receiving.show', $receiving->ReceivingID) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Details
        </a>
    </div>

    <form action="{{ route('receiving.update', $receiving->ReceivingID) }}" method="POST" id="receivingForm">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Purchase Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th width="30%">PO Number:</th>
                                    <td>{{ $receiving->purchaseOrder->PONumber }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier:</th>
                                    <td>{{ $receiving->purchaseOrder->supplier->CompanyName }}</td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td>{{ $receiving->purchaseOrder->OrderDate->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Ordered Qty</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receiving->purchaseOrder->items as $item)
                                    <tr>
                                        <td>{{ $item->item->ItemName }}</td>
                                        <td>{{ $item->Quantity }}</td>
                                        <td>₱{{ number_format($item->UnitPrice, 2) }}</td>
                                        <td>₱{{ number_format($item->TotalPrice, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                        <td>₱{{ number_format($receiving->purchaseOrder->TotalAmount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
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
                                   value="{{ $receiving->DateReceived->format('M d, Y') }}" 
                                   readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="Notes" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Enter any notes here...">{{ $receiving->Notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Update Receiving
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection 