@forelse($purchases as $po)
<tr>
    <td>
        <div class="btn-group" role="group">
            <a href="{{ route('purchases.show', $po->PurchaseOrderID) }}" 
               class="btn btn-sm btn-info" 
               title="View">
                <i class="bi bi-eye"></i>
            </a>
            <button type="button" 
                    class="btn btn-sm btn-danger" 
                    data-bs-toggle="modal"
                    data-bs-target="#deletePurchaseModal{{ $po->PurchaseOrderID }}"
                    title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
    <td>{{ $po->PONumber }}</td>
    <td>{{ $po->supplier->CompanyName }}</td>
    <td>{{ $po->OrderDate->format('M d, Y') }}</td>
    <td>
        <span class="badge bg-{{ $po->Status === 'Pending' ? 'warning' : 'success' }}">
            {{ $po->Status }}
        </span>
    </td>
    <td>₱{{ number_format($po->getTotalAmount(), 2) }}</td>
    <td>{{ $po->createdBy->FirstName ?? 'N/A' }} {{ $po->createdBy->LastName ?? '' }}</td>
    <td>{{ $po->DateCreated ? date('Y-m-d H:i:s', strtotime($po->DateCreated)) : 'N/A' }}</td>
    <td>{{ $po->modifiedBy->FirstName ?? 'N/A' }} {{ $po->modifiedBy->LastName ?? '' }}</td>
    <td>{{ $po->DateModified ? date('Y-m-d H:i:s', strtotime($po->DateModified)) : 'N/A' }}</td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center">No purchase orders found</td>
</tr>
@endforelse 