@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Reagent Item</h3>
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
            <form method="POST" action="{{ route('reagent-items.update', $item->reagent_item_id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $item->quantity) }}" required>
                </div>
                <div class="mb-3">
                    <label>Reagent</label>
                    <input type="text" name="reagent" class="form-control" value="{{ old('reagent', $item->reagent) }}" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="sds_checked" class="form-check-input" id="sds_checked" {{ old('sds_checked', $item->sds_checked) ? 'checked' : '' }}>
                    <label class="form-check-label" for="sds_checked">SDS Checked</label>
                </div>
                <div class="mb-3">
                    <label>Issued Amount</label>
                    <input type="text" name="issued_amount" class="form-control" value="{{ old('issued_amount', $item->issued_amount) }}">
                </div>
                <div class="mb-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control">{{ old('remarks', $item->remarks) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('reagent-items.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection 