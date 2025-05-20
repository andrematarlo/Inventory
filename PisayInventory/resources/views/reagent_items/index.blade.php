@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Reagent Items Master List</h3>
            <a href="{{ route('reagent-items.create') }}" class="btn btn-primary">Add New</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Quantity</th>
                            <th>Reagent</th>
                            <th>SDS Checked</th>
                            <th>Issued Amount</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->reagent_item_id }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->reagent }}</td>
                            <td>{{ $item->sds_checked ? 'Yes' : 'No' }}</td>
                            <td>{{ $item->issued_amount }}</td>
                            <td>{{ $item->remarks }}</td>
                            <td>
                                <a href="{{ route('reagent-items.edit', $item->reagent_item_id) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('reagent-items.destroy', $item->reagent_item_id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No reagent items found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 