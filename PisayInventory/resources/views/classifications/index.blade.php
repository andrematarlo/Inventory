@extends('layouts.app')

@section('title', 'Classifications')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Classification Management</h2>
        {{-- Show Add button only if user has Add permission --}}
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassificationModal">
            <i class="bi bi-plus-lg"></i> Add Classification
        </button>
        @endif
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Active Classifications</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <th>Actions</th>
                            @endif
                            <th>Name</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classifications as $classification)
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <td>
                                <div class="btn-group">
                                    @if($userPermissions->CanEdit)
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editClassificationModal{{ $classification->ClassificationId }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @endif
                                    @if($userPermissions->CanDelete)
                                    <form action="{{ route('classifications.destroy', $classification->ClassificationId) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this classification?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                            @endif
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateCreated ? date('M d, Y h:i A', strtotime($classification->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $classification->modified_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateModified ? date('M d, Y h:i A', strtotime($classification->DateModified)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)) ? '6' : '5' }}" class="text-center">No classifications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-center mt-3">
                {{ $classifications->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>

    <!-- Deleted Classifications Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Deleted Classifications</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">Actions</th>
                            <th>Name</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedClassifications as $classification)
                        <tr>
                            <td class="text-center">
                                {{-- Show restore button only if not Inventory Manager or Inventory Staff --}}
                                @if(auth()->user()->role !== 'Inventory Manager' && auth()->user()->role !== 'Inventory Staff')
                                <form action="{{ route('classifications.restore', $classification->ClassificationId) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->deleted_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateDeleted ? date('M d, Y h:i A', strtotime($classification->DateDeleted)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No deleted classifications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-center mt-3">
                {{ $trashedClassifications->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Classification Modal -->
@if($userPermissions && $userPermissions->CanAdd)
    @include('classifications.partials.add-modal')
@endif

<!-- Edit Classification Modals -->
@if($userPermissions && $userPermissions->CanEdit)
    @foreach($classifications as $classification)
        @include('classifications.partials.edit-modal', ['classification' => $classification])
    @endforeach
@endif

@endsection

@section('scripts')
<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}
</script>
@endsection