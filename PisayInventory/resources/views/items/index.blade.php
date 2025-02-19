@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Items Management</h2>
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg"></i> New Item
        </button>
        @endif
    </div>

    <div class="btn-group mb-4" role="group">
        <button class="btn btn-primary active" type="button" id="activeRecordsBtn">
            Active Records
        </button>
        <button class="btn btn-danger" type="button" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Active Items Section -->
    <div id="activeItems">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Active Items</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>Showing {{ $activeItems->firstItem() ?? 0 }} to {{ $activeItems->lastItem() ?? 0 }} of {{ $activeItems->total() }} results</div>
                    <div class="pagination-sm">
                        @if($activeItems->currentPage() > 1)
                            <a href="{{ $activeItems->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        @endif
                        
                        @for($i = 1; $i <= $activeItems->lastPage(); $i++)
                            <a href="{{ $activeItems->url($i) }}" 
                               class="btn btn-sm {{ $i == $activeItems->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if($activeItems->hasMorePages())
                            <a href="{{ $activeItems->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Actions</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Classification</th>
                                <th>Unit</th>
                                <th>Stocks</th>
                                <th>Reorder Point</th>
                                <th>Created By</th>
                                <th>Date Created</th>
                                <th>Modified By</th>
                                <th>Date Modified</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeItems as $item)
                                <tr>
                                    @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($userPermissions->CanEdit)
                                            <button type="button" class="btn btn-sm btn-blue" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->ItemId }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @endif
                                            @if($userPermissions && $userPermissions->CanDelete)
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->ItemId }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                    @endif
                                    <td>
                                        @if($item->ImagePath)
                                            <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                                 alt="{{ $item->ItemName }}" 
                                                 style="height: 50px; width: 50px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->ItemName }}</td>
                                    <td>{{ $item->Description }}</td>
                                    <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                                    <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                                    <td>{{ $item->StocksAvailable }}</td>
                                    <td>{{ $item->ReorderPoint }}</td>
                                    <td>{{ $item->createdBy->Username ?? 'N/A' }}</td>
                                    <td>{{ $item->DateCreated ? date('Y-m-d H:i:s', strtotime($item->DateCreated)) : 'N/A' }}</td>
                                    <td>{{ $item->modifiedBy->Username ?? 'N/A' }}</td>
                                    <td>{{ $item->DateModified ? date('Y-m-d H:i:s', strtotime($item->DateModified)) : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center">No active items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Deleted Items Section -->
    <div id="deletedItems" style="display: none;">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Deleted Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="deletedItemsTable">
                        <thead>
                            <tr>
                                <th>Actions</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Classification</th>
                                <th>Unit</th>
                                <th>Stocks</th>
                                <th>Deleted By</th>
                                <th>Date Deleted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deletedItems as $item)
                                <tr>
                                    <td>
                                        <form action="{{ route('items.restore', $item->ItemId) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($item->ImagePath)
                                            <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                                 alt="{{ $item->ItemName }}" 
                                                 style="height: 50px; width: 50px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->ItemName }}</td>
                                    <td>{{ $item->Description }}</td>
                                    <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                                    <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                                    <td>{{ $item->StocksAvailable }}</td>
                                    <td>{{ $item->deletedBy->Username ?? 'N/A' }}</td>
                                    <td>{{ $item->DateDeleted ? date('Y-m-d H:i:s', strtotime($item->DateDeleted)) : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No deleted items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $deletedItems->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($userPermissions && $userPermissions->CanAdd)
    @include('items.partials.add-modal')
@endif

@if($userPermissions && $userPermissions->CanEdit)
    @foreach($activeItems as $item)
        @include('items.partials.edit-modal', ['item' => $item])
    @endforeach
@endif

@include('items.partials.delete-modal')
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Toggle between active and deleted items
        $('#activeRecordsBtn, #showDeletedBtn').on('click', function() {
            const activeDiv = $('#activeItems');
            const deletedDiv = $('#deletedItems');
            const activeBtn = $('#activeRecordsBtn');
            const deletedBtn = $('#showDeletedBtn');

            if ($(this).attr('id') === 'showDeletedBtn') {
                activeDiv.hide();
                deletedDiv.show();
                activeBtn.removeClass('active');
                deletedBtn.addClass('active');
            } else {
                deletedDiv.hide();
                activeDiv.show();
                deletedBtn.removeClass('active');
                activeBtn.addClass('active');
            }
        });

        // Remove any existing success alerts at the top of the page
        $('.alert-success:not(.fade)').remove();
        
        // Auto dismiss alerts after 3 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 3000);
    });
</script>
@endsection

@section('additional_styles')
<style>
    .btn-blue {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-blue:hover {
        background-color: #0b5ed7;
        color: white;
    }

    /* Pagination styling */
    .pagination-sm {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .pagination-sm .btn {
        min-width: 32px;
        padding: 4px 8px;
        font-size: 0.875rem;
    }

    .pagination-sm .btn i {
        font-size: 12px;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }

    .btn-primary {
        background-color: #3498db;
        border-color: #3498db;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        border-color: #2980b9;
    }

    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .btn-group .btn.active {
        opacity: 1;
    }

    .btn-group .btn:not(.active) {
        opacity: 0.8;
    }

    .btn-group .btn:hover:not(.active) {
        opacity: 0.9;
    }
</style>
@endsection
