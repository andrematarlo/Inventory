@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Point of Sale (POS) Report</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Purchases</span>
                                    <span class="info-box-number">{{ $summary['total_purchases'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Quantity</span>
                                    <span class="info-box-number">{{ $summary['total_quantity'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-cubes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Stocks Added</span>
                                    <span class="info-box-number">{{ $summary['total_stocks_added'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Classification</th>
                                <th>Unit of Measure</th>
                                <th>Quantity</th>
                                <th>Stocks Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posData as $purchase)
                            <tr>
                                <td>{{ $purchase->DateCreated ? \Carbon\Carbon::parse($purchase->DateCreated)->format('M d, Y H:i') : 'N/A' }}</td>
                                <td>{{ $purchase->item->ItemName ?? 'N/A' }}</td>
                                <td>{{ $purchase->classification->ClassificationName ?? 'N/A' }}</td>
                                <td>{{ $purchase->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                                <td>{{ $purchase->Quantity }}</td>
                                <td>{{ $purchase->StocksAdded }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        // Destroy existing DataTables if they exist
        if ($.fn.DataTable.isDataTable('.table')) {
            $('.table').DataTable().destroy();
        }

        // Initialize DataTables with unique IDs
        $('.table').each(function() {
            $(this).DataTable({
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
            });
        });
    });
</script>
@endsection
