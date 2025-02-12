@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sales Report</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">
                            {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Sales Volume</span>
                                    <span class="info-box-number">{{ $summary['total_sales_volume'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-box"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Unique Items Sold</span>
                                    <span class="info-box-number">{{ $summary['total_unique_items'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Sales Value</span>
                                    <span class="info-box-number">{{ number_format($summary['total_sales_value'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mt-4 mb-3">Sales by Item</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Classification</th>
                                <th>Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesByItem as $itemSales)
                            <tr>
                                <td>{{ $itemSales['item_name'] }}</td>
                                <td>{{ $itemSales['classification'] }}</td>
                                <td>{{ $itemSales['total_quantity'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <h4 class="mt-4 mb-3">Detailed Sales Transactions</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Classification</th>
                                <th>Unit of Measure</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesData as $sale)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sale->DateCreated)->format('M d, Y H:i') }}</td>
                                <td>{{ $sale->item->ItemName ?? 'N/A' }}</td>
                                <td>{{ $sale->classification->ClassificationName ?? 'N/A' }}</td>
                                <td>{{ $sale->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                                <td>{{ $sale->Quantity }}</td>
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