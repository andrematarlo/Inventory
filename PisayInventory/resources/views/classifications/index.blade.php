@extends('layouts.app')

@section('title', 'Classifications')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <span>Show</span>
                    <select class="form-select form-select-sm mx-2" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>classifications per page</span>
                </div>
                <div class="d-flex gap-2">
                    <input type="search" class="form-control form-control-sm" placeholder="Search classifications...">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addClassificationModal">
                        <i class="bi bi-plus-lg"></i> Add Classification
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classifications as $classification)
                        <tr>
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateCreated ? date('M d, Y h:i A', strtotime($classification->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $classification->modified_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateModified ? date('M d, Y h:i A', strtotime($classification->DateModified)) : 'N/A' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editClassificationModal{{ $classification->ClassificationId }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('classifications.destroy', $classification->ClassificationId) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this classification?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No classifications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>Showing 1 to {{ $classifications->count() }} of {{ $classifications->count() }} classifications</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Deleted Classifications Card -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <span>Show</span>
                    <select class="form-select form-select-sm mx-2" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>deleted classifications per page</span>
                </div>
                <input type="search" class="form-control form-control-sm" style="width: 200px;" placeholder="Search deleted classifications...">
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedClassifications ?? [] as $classification)
                        <tr>
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->deleted_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateDeleted ? date('M d, Y h:i A', strtotime($classification->DateDeleted)) : 'N/A' }}</td>
                            <td class="text-end">
                                <form action="{{ route('classifications.restore', $classification->ClassificationId) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No deleted classifications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>Showing 1 to {{ ($trashedClassifications ?? collect())->count() }} of {{ ($trashedClassifications ?? collect())->count() }} deleted classifications</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Classification Modal -->
<div class="modal fade" id="addClassificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Classification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('classifications.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Classification Name</label>
                        <input type="text" class="form-control" name="ClassificationName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Classification Modals -->
@foreach($classifications as $classification)
<div class="modal fade" id="editClassificationModal{{ $classification->ClassificationId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Classification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('classifications.update', $classification->ClassificationId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Classification Name</label>
                        <input type="text" class="form-control" name="ClassificationName" value="{{ $classification->ClassificationName }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection