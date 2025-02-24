<!-- Import Excel Modal -->
<div class="modal fade" 
     id="importExcelModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1"
     aria-labelledby="importExcelModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">Import Items from Excel</h5>
                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="modal" 
                        aria-label="Close"></button>
            </div>
            <form id="importForm" action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Excel File</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>

                    <!-- Column Mapping -->
                    <div class="mb-3">
                        <h6>Column Mapping</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="ItemName" class="form-label">Item Name Column</label>
                                <input type="text" class="form-control" id="ItemName" name="column_mapping[ItemName]" required>
                            </div>
                            <div class="col-md-6">
                                <label for="Description" class="form-label">Description Column</label>
                                <input type="text" class="form-control" id="Description" name="column_mapping[Description]">
                            </div>
                            <div class="col-md-6">
                                <label for="StocksAvailable" class="form-label">Stocks Column</label>
                                <input type="text" class="form-control" id="StocksAvailable" name="column_mapping[StocksAvailable]">
                            </div>
                            <div class="col-md-6">
                                <label for="ReorderPoint" class="form-label">Reorder Point Column</label>
                                <input type="text" class="form-control" id="ReorderPoint" name="column_mapping[ReorderPoint]">
                            </div>
                        </div>
                    </div>

                    <!-- Default Values -->
                    <div class="mb-3">
                        <h6>Default Values</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="default_classification" class="form-label">Classification</label>
                                <select class="form-select" id="default_classification" name="default_classification" required>
                                    @foreach($classifications as $classification)
                                        <option value="{{ $classification->ClassificationId }}">
                                            {{ $classification->ClassificationName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="default_unit" class="form-label">Unit</label>
                                <select class="form-select" id="default_unit" name="default_unit" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->UnitOfMeasureId }}">
                                            {{ $unit->UnitName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="default_stocks" class="form-label">Default Stocks</label>
                                <input type="number" class="form-control" id="default_stocks" name="default_stocks" value="0">
                            </div>
                            <div class="col-md-6">
                                <label for="default_reorder_point" class="form-label">Default Reorder Point</label>
                                <input type="number" class="form-control" id="default_reorder_point" name="default_reorder_point" value="10">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize import modal
    const importModal = document.getElementById('importExcelModal');
    if (importModal) {
        const bsModal = new bootstrap.Modal(importModal, {
            backdrop: 'static',
            keyboard: false
        });

        // Prevent modal from closing when clicking outside
        $(importModal).on('click', function(e) {
            if ($(e.target).is('.modal')) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Handle form submission
        $('#importForm').on('submit', function(e) {
            e.preventDefault();
            
            // ... rest of your form submission code ...
        });
    }
});
</script>
@endpush 