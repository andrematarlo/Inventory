@extends('layouts.app')

@section('title', 'View Receiving')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Receiving Details</h1>
        <a href="{{ route('receiving.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Purchase Order Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
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
                        <tr>
                            <th>Total Amount:</th>
                            <td>₱{{ number_format($receiving->purchaseOrder->TotalAmount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Receiving Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Date Received:</th>
                            <td>{{ $receiving->DateReceived->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $receiving->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $receiving->Status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Received By:</th>
                            <td>{{ $receiving->receivedBy->FirstName }} {{ $receiving->receivedBy->LastName }}</td>
                        </tr>
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $receiving->Notes ?? 'No notes' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Received Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
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
                            <td colspan="3" class="text-end fw-bold">Total Amount:</td>
                            <td class="fw-bold">₱{{ number_format($receiving->purchaseOrder->TotalAmount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Audit Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Created By:</th>
                            <td>{{ $receiving->createdBy->FirstName }} {{ $receiving->createdBy->LastName }}</td>
                        </tr>
                        <tr>
                            <th>Created Date:</th>
                            <td>{{ $receiving->DateCreated->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if($receiving->ModifiedByID)
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Modified By:</th>
                            <td>{{ $receiving->modifiedBy->FirstName }} {{ $receiving->modifiedBy->LastName }}</td>
                        </tr>
                        <tr>
                            <th>Modified Date:</th>
                            <td>{{ $receiving->DateModified->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 