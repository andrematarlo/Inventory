@extends('layouts.app')

@section('title', 'Deleted Inventory Records')

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
                                    <form action="{{ route('inventory.restore', $inventory->InventoryId) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to restore this record?');">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
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
