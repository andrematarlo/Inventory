@extends('layouts.app')

@section('title', 'Trash - Classifications')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Trash - Classifications</h2>
        <a href="{{ route('classifications.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Classifications
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                Show 
                <select class="form-select form-select-sm d-inline-block w-auto">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select> 
                entries
            </div>
            <div>
                <input type="search" class="form-control form-control-sm" placeholder="Search..." aria-label="Search">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name <i class="bi bi-arrow-up-short"></i></th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedClassifications as $classification)
                        <tr>
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->deleted_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateDeleted ? date('Y-m-d H:i', strtotime($classification->DateDeleted)) : 'N/A' }}</td>
                            <td class="text-end">
                                <form action="{{ route('classifications.restore', $classification->ClassificationId) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-trash text-muted" style="font-size: 2rem;"></i>
                                    <h5 class="mt-2 mb-1">No Deleted Classifications</h5>
                                    <p class="text-muted mb-0">Deleted classifications will appear here</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                Showing <span class="fw-semibold">{{ $trashedClassifications->count() }}</span> entries
            </div>
            <nav aria-label="Table navigation">
                <ul class="pagination justify-content-end mb-0">
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
@endsection
