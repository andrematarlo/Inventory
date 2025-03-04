@extends('layouts.app')

@section('title', 'Deleted Inventory Records')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Deleted Inventory Records</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Active Records
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Classification</th>
                            <th>Stocks In</th>
                            <th>Stocks Out</th>
                            <th>Stocks Available</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedInventories as $inventory)
                        <tr>
                            <td>{{ $inventory->item->ItemName ?? 'N/A' }}</td>
                            <td>{{ $inventory->item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>{{ $inventory->StocksAdded }}</td>
                            <td>{{ $inventory->StockOut }}</td>
                            <td>{{ $inventory->StocksAvailable }}</td>
                            <td>{{ $inventory->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ date('Y-m-d h:i:s A', strtotime($inventory->DateCreated)) }}</td>
                            <td>{{ optional($inventory->deleted_by_user)->Username ?? 'N/A' }}</td>
                            <td>{{ $inventory->DateDeleted ? date('Y-m-d H:i', strtotime($inventory->DateDeleted)) : 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-success restore-inventory"
                                            data-inventory-id="{{ $inventory->InventoryId }}"
                                            data-item-name="{{ $inventory->item->ItemName }}">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No deleted inventory records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $trashedInventories->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('.restore-inventory').click(function(e) {
        e.preventDefault();
        const inventoryId = $(this).data('inventory-id');
        const itemName = $(this).data('item-name');

        Swal.fire({
            title: 'Restore Inventory Record?',
            html: `Are you sure you want to restore the inventory record for: <strong>${itemName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/inventory/${inventoryId}/restore`;
                form.innerHTML = `
                    @csrf
                    @method('PUT')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection
