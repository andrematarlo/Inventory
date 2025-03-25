@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Menu Item: {{ $menuItem->name }}</h3>
                    <a href="{{ route('pos.menu-items.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Menu Items
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('pos.menu-items.update', $menuItem->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $menuItem->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="price">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $menuItem->price) }}" required>
                                    </div>
                                    @error('price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="category">Category <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category', $menuItem->category) }}" required>
                                    @error('category')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $menuItem->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Menu Item Image</label>
                                    
                                    @if($menuItem->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $menuItem->image) }}" alt="{{ $menuItem->name }}" class="img-thumbnail" style="max-height: 150px;">
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="checkbox" class="custom-control-input" id="removeImage" name="remove_image">
                                            <label class="custom-control-label" for="removeImage">Remove current image</label>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image">
                                        <label class="custom-file-label" for="image">Choose new image</label>
                                    </div>
                                    <small class="form-text text-muted">Upload a square image for best results. Maximum size: 2MB</small>
                                    @error('image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="available" name="available" value="1" {{ old('available', $menuItem->available) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="available">Available for ordering</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-right mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Menu Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Update file input label to show selected filename
    $(document).ready(function() {
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
        
        // Disable file input if remove image is checked
        $("#removeImage").on("change", function() {
            if($(this).is(":checked")) {
                $("#image").prop("disabled", true);
                $(".custom-file-label").text("Current image will be removed");
            } else {
                $("#image").prop("disabled", false);
                $(".custom-file-label").text("Choose new image");
            }
        });
    });
</script>
@endsection 