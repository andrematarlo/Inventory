@foreach($order->items as $item)
<tr>
    <td>{{ $item->ItemName }}</td>
    <td>{{ $item->Quantity }}</td>
    <td class="text-end">₱{{ number_format($item->UnitPrice, 2) }}</td>
    <td class="text-end">₱{{ number_format($item->Subtotal, 2) }}</td>
</tr>
@endforeach
<tr class="table-light">
    <td colspan="3" class="text-end"><strong>Total:</strong></td>
    <td class="text-end"><strong>₱{{ number_format($order->TotalAmount, 2) }}</strong></td>
</tr> 