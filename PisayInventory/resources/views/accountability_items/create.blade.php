@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Accountability Item</h3>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('accountability-items.store') }}">
                @csrf
                <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" required>
                </div>
                <div class="mb-3">
                    <label>Item</label>
                    <input type="text" name="item" class="form-control" value="{{ old('item') }}" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label>Issued Condition</label>
                    <input type="text" name="issued_condition" class="form-control" value="{{ old('issued_condition') }}">
                </div>
                <div class="mb-3">
                    <label>Returned Condition</label>
                    <input type="text" name="returned_condition" class="form-control" value="{{ old('returned_condition') }}">
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('accountability-items.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection 