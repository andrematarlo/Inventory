@extends('layouts.app')

@section('title', 'View Purchase Order')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Purchase Order Details</h1>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
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
                            <td>{{ $purchase->PONumber }}</td>
                        </tr>
                        <tr>
                            <th>Supplier:</th>
                            <td>{{ $purchase->supplier->CompanyName }}</td>
                        </tr>
                        <tr>
                            <th>Order Date:</th>
                            <td>{{ $purchase->OrderDate->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $purchase->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $purchase->Status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td>₱{{ number_format($purchase->TotalAmount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Audit Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Created By:</th>
                            <td>{{ $purchase->createdBy->FirstName }} {{ $purchase->createdBy->LastName }}</td>
                        </tr>
                        <tr>
                            <th>Created Date:</th>
                            <td>{{ $purchase->DateCreated->format('M d, Y h:i A') }}</td>
                        </tr>
                        @if($purchase->ModifiedByID)
                        <tr>
                            <th>Modified By:</th>
                            <td>{{ $purchase->modifiedBy->FirstName }} {{ $purchase->modifiedBy->LastName }}</td>
                        </tr>
                        <tr>
                            <th>Modified Date:</th>
                            <td>{{ $purchase->DateModified->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Purchase Order Items</h5>
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
                        @foreach($purchase->items as $item)
                        <tr>
                            <td>{{ $item->item->ItemName }}</td>
                            <td>{{ $item->Quantity }}</td>
                            <td>₱{{ number_format($item->UnitPrice, 2) }}</td>
                            <td>₱{{ number_format($item->TotalPrice, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Amount:</td>
                            <td class="fw-bold">₱{{ number_format($purchase->TotalAmount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 