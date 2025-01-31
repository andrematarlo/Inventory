@extends('layouts.app')

@section('title', 'Classifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Classifications Management</h2>
    <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addClassificationModal">
        <i class="bi bi-plus-lg"></i> Add New Classification
    </button>
</div>

<!-- Classifications Table -->
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Classification Name</th>
                        <th>Parent Classification</th>
                        <th>Created By</th>
                        <th>Date Created</th>
                        <th>Modified By</th>
                        <th>Date Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classifications as $classification)
                    <tr @if($classification->ParentClassificationId) class="nested-classification" @endif>
                        <td>{{ $classification->ClassificationName }}</td>
                        <td>
                            @if($classification->parent)
                                {{ $classification->parent->ClassificationName }}
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </td>
                        <td>{{ $classification->createdBy->Username ?? 'N/A' }}</td>
                        <td>{{ $classification->DateCreated ? date('Y-m-d H:i', strtotime($classification->DateCreated)) : 'N/A' }}</td>
                        <td>{{ $classification->modifiedBy->Username ?? 'N/A' }}</td>
                        <td>{{ $classification->DateModified ? date('Y-m-d H:i', strtotime($classification->DateModified)) : 'N/A' }}</td>
                        <td class="action-buttons">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#editClassificationModal{{ $classification->ClassificationId }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#deleteClassificationModal{{ $classification->ClassificationId }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No classifications found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Classification Modal -->
<div class="modal fade" id="addClassificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Classification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('classifications.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Classification Name</label>
                        <input type="text" class="form-control" name="ClassificationName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Classification</label>
                        <select class="form-select" name="ParentClassificationId">
                            <option value="">None</option>
                            @foreach($parentClassifications as $parent)
                                <option value="{{ $parent->ClassificationId }}">
                                    {{ $parent->ClassificationName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Classification</button>
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
                        <input type="text" class="form-control" name="ClassificationName" 
                               value="{{ $classification->ClassificationName }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Classification</label>
                        <select class="form-select" name="ParentClassificationId">
                            <option value="">None</option>
                            @foreach($parentClassifications as $parent)
                                @if($parent->ClassificationId != $classification->ClassificationId)
                                    <option value="{{ $parent->ClassificationId }}"
                                        {{ $classification->ParentClassificationId == $parent->ClassificationId ? 'selected' : '' }}>
                                        {{ $parent->ClassificationName }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Classification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Delete Classification Modals -->
@foreach($classifications as $classification)
<div class="modal fade" id="deleteClassificationModal{{ $classification->ClassificationId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Classification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('classifications.destroy', $classification->ClassificationId) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this classification?</p>
                    <p class="text-danger"><strong>{{ $classification->ClassificationName }}</strong></p>
                    @if($classification->children->count() > 0)
                        <div class="alert alert-warning">
                            Warning: This classification has sub-classifications that will also be deleted.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Classification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection 