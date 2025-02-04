@extends('layouts.app')

@section('title', 'Classifications')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Classifications</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassificationModal">
            <i class="bi bi-plus-lg"></i> Add Classification
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classifications as $classification)
                        <tr>
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateCreated ? date('Y-m-d H:i', strtotime($classification->DateCreated)) : 'N/A' }}</td>
                            <td>
                                <!-- Add your action buttons here -->
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No classifications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
@endsection 