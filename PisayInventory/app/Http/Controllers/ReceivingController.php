<?php

namespace App\Http\Controllers;

use App\Models\Receiving;
use App\Models\PurchaseOrder;
use App\Models\Employee;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceivingController extends Controller
{
    public function index()
    {
        try {
            // First check all receiving records
            $allRecords = Receiving::where('IsDeleted', false)->get();
            Log::info('All receiving records:', [
                'total_count' => $allRecords->count(),
                'status_breakdown' => $allRecords->groupBy('Status')->map->count()
            ]);

            // Active receiving records
            $receivingRecords = Receiving::with([
                'purchaseOrder.supplier',
                'createdBy',
                'modifiedBy'
            ])
            ->where('IsDeleted', false)
            ->where('Status', 'Received')
            ->orderBy('DateCreated', 'desc')
            ->get();

            // Pending records from purchase orders
            $pendingRecords = PurchaseOrder::with([
                'supplier',
                'createdBy',
                'modifiedBy'
            ])
            ->where('IsDeleted', false)
            ->where('Status', 'Pending')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('receiving')
                      ->whereRaw('receiving.PurchaseOrderID = purchase_order.PurchaseOrderID')
                      ->where('receiving.IsDeleted', false);
            })
            ->orderBy('DateCreated', 'desc')
            ->get();

            // Partial receiving records
            $partialRecords = Receiving::with([
                'purchaseOrder.supplier',
                'purchaseOrder.items.item',
                'createdBy',
                'modifiedBy'
            ])
            ->where('IsDeleted', false)
            ->where('Status', 'Partial')
            ->orderBy('DateCreated', 'desc')
            ->get();

            // Log the exact query being executed
            Log::info('Partial records query:', [
                'sql' => Receiving::where('IsDeleted', false)
                    ->where('Status', 'Partial')
                    ->toSql(),
                'bindings' => Receiving::where('IsDeleted', false)
                    ->where('Status', 'Partial')
                    ->getBindings()
            ]);

            // Log detailed info about each partial record
            foreach ($partialRecords as $record) {
                Log::info("Partial record {$record->ReceivingID}:", [
                    'status' => $record->Status,
                    'is_deleted' => $record->IsDeleted,
                    'po_number' => $record->purchaseOrder->PONumber ?? 'N/A',
                    'item_statuses' => $record->ItemStatuses,
                    'date_received' => $record->DateReceived
                ]);
            }

            // Deleted receiving records
            $deletedRecords = Receiving::with([
                'purchaseOrder.supplier',
                'createdBy',
                'modifiedBy',
                'deletedBy'
            ])
            ->where('IsDeleted', true)
            ->orderBy('DateDeleted', 'desc')
            ->get();

            // Debug logging
            Log::info('Active records count: ' . $receivingRecords->count());
            Log::info('Pending records count: ' . $pendingRecords->count());
            Log::info('Partial records count: ' . $partialRecords->count());
            Log::info('Deleted records count: ' . $deletedRecords->count());

            Log::info('Partial records details:', [
                'count' => $partialRecords->count(),
                'records' => $partialRecords->map(function($record) {
                    return [
                        'id' => $record->ReceivingID,
                        'po_number' => $record->purchaseOrder->PONumber ?? 'N/A',
                        'status' => $record->Status,
                        'po_status' => $record->purchaseOrder->Status ?? 'N/A',
                        'is_deleted' => $record->IsDeleted,
                        'receiving_date' => $record->DateReceived,
                        'supplier' => $record->purchaseOrder->supplier->CompanyName ?? 'N/A'
                    ];
                })->toArray()
            ]);

            return view('receiving.index', compact(
                'receivingRecords', 
                'pendingRecords', 
                'partialRecords',
                'deletedRecords'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading receiving records: ' . $e->getMessage());
            return back()->with('error', 'Error loading receiving records: ' . $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            if ($request->has('po_id')) {
                $purchaseOrder = PurchaseOrder::with(['supplier', 'items.item'])
                    ->where('PurchaseOrderID', $request->po_id)
                    ->firstOrFail();

                // Get latest receiving record for this PO
                $existingReceiving = Receiving::where('PurchaseOrderID', $purchaseOrder->PurchaseOrderID)
                    ->where('IsDeleted', false)
                    ->latest('DateCreated')
                    ->first();

                // Calculate already received quantities for each item
                foreach ($purchaseOrder->items as $item) {
                    if ($existingReceiving) {
                        $itemStatuses = json_decode($existingReceiving->ItemStatuses, true) ?? [];
                        $itemStatus = $itemStatuses[$item->ItemId] ?? ['status' => 'Pending', 'received_qty' => 0];
                        
                        // Set status and received quantity
                        $item->status = $itemStatus['status'] ?? 'Pending';
                        $item->received_qty = $itemStatus['received_qty'] ?? 0;
                    } else {
                        $item->received_qty = 0;
                        $item->status = 'Pending';
                    }
                    
                    $item->remaining_qty = max(0, $item->Quantity - $item->received_qty);

                    // Debug log
                    Log::info("Item {$item->ItemId} status:", [
                        'received_qty' => $item->received_qty,
                        'remaining_qty' => $item->remaining_qty,
                        'total_quantity' => $item->Quantity,
                        'status' => $item->status,
                        'status_type' => gettype($item->status)
                    ]);
                }

                return view('receiving.create', compact('purchaseOrder', 'existingReceiving'));
            }

            // Otherwise, get all pending POs
            $pendingPOs = PurchaseOrder::where('Status', 'Pending')
                ->where('IsDeleted', false)
                ->with(['supplier', 'items.item'])
                ->get();

            return view('receiving.create', compact('pendingPOs'));
        } catch (\Exception $e) {
            Log::error('Error loading create receiving form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            date_default_timezone_set('Asia/Manila');

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $purchaseOrder = PurchaseOrder::with(['items.item'])->findOrFail($request->PurchaseOrderID);

            // Check for existing receiving record
            $existingReceiving = Receiving::where('PurchaseOrderID', $purchaseOrder->PurchaseOrderID)
                ->where('IsDeleted', false)
                ->latest('DateCreated')
                ->first();

            // Get existing item statuses if any
            $existingStatuses = [];
            if ($existingReceiving) {
                $existingStatuses = json_decode($existingReceiving->ItemStatuses, true) ?? [];
            }

            // Check if all items are fully received
            $isFullyReceived = true;
            $itemStatuses = [];
            $hasPartialItems = false;
            $totalItems = 0;
            $completeItems = 0;

            foreach ($purchaseOrder->items as $poItem) {
                $newQty = (int)$request->input("items.{$poItem->ItemId}.quantity", 0);
                $totalItems++;
                
                // Get existing received quantity from ItemStatuses
                $existingQty = 0;
                if (isset($existingStatuses[$poItem->ItemId])) {
                    $existingStatus = $existingStatuses[$poItem->ItemId];
                    $existingQty = is_array($existingStatus) ? ($existingStatus['received_qty'] ?? 0) : 0;
                }
                
                $totalReceivedQty = $existingQty + $newQty;
                
                // Store the status with structure
                if ($totalReceivedQty >= $poItem->Quantity) {
                    $itemStatuses[$poItem->ItemId] = [
                        'status' => 'Complete',
                        'received_qty' => $totalReceivedQty
                    ];
                    $completeItems++;
                } elseif ($totalReceivedQty > 0) {
                    $itemStatuses[$poItem->ItemId] = [
                        'status' => 'Partial',
                        'received_qty' => $totalReceivedQty
                    ];
                    $hasPartialItems = true;
                    $isFullyReceived = false;
                } else {
                    // If no new quantity and no existing quantity, mark as Pending
                    $itemStatuses[$poItem->ItemId] = [
                        'status' => 'Pending',
                        'received_qty' => 0
                    ];
                    $isFullyReceived = false;
                }

                // Debug log
                Log::info("Processing item {$poItem->ItemId}:", [
                    'new_qty' => $newQty,
                    'existing_qty' => $existingQty,
                    'total_received' => $totalReceivedQty,
                    'ordered_qty' => $poItem->Quantity,
                    'status' => $itemStatuses[$poItem->ItemId]['status']
                ]);
            }

            // Determine overall status based on all items
            $status = 'Pending';
            if ($completeItems === $totalItems) {
                $status = 'Received';
            } elseif ($completeItems > 0 || $hasPartialItems) {
                $status = 'Partial';
            }

            // Update existing receiving record if it exists
            if ($existingReceiving) {
                $existingReceiving->update([
                    'Status' => $status,
                    'Notes' => $request->Notes,
                    'ModifiedByID' => $currentEmployee->EmployeeID,
                    'DateModified' => date('Y-m-d'),
                    'ItemStatuses' => json_encode($itemStatuses)
                ]);
                $receiving = $existingReceiving;
            } else {
                // Create new receiving record
                $receiving = new Receiving([
                    'PurchaseOrderID' => $request->PurchaseOrderID,
                    'ReceivedByID' => $currentEmployee->EmployeeID,
                    'DateReceived' => $request->DeliveryDate ?? date('Y-m-d'),
                    'Status' => $status,
                    'Notes' => $request->Notes,
                    'DateCreated' => date('Y-m-d'),
                    'CreatedByID' => $currentEmployee->EmployeeID,
                    'IsDeleted' => false,
                    'ItemStatuses' => json_encode($itemStatuses)
                ]);
                $receiving->save();
            }

            // Update purchase order status
            $purchaseOrder->update([
                'Status' => $status,
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            // Update inventory only for newly received items
            foreach ($purchaseOrder->items as $poItem) {
                $newQty = (int)$request->input("items.{$poItem->ItemId}.quantity", 0);
                if ($newQty > 0) {
                    $item = $poItem->item;
                    $item->StocksAvailable += $newQty;
                    $item->save();

                    Inventory::create([
                        'ItemId' => $poItem->ItemId,
                        'PurchaseOrderID' => $purchaseOrder->PurchaseOrderID,
                        'StocksAdded' => $newQty,
                        'StocksAvailable' => $item->StocksAvailable,
                        'DateCreated' => now(),
                        'CreatedByID' => $currentEmployee->EmployeeID,
                        'IsDeleted' => false
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('receiving.show', $receiving->ReceivingID)
                ->with('success', $status === 'Received' ? 'All items received successfully' : 'Items partially received');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating receiving record: ' . $e->getMessage());
            return back()->with('error', 'Error creating receiving record: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $receiving = Receiving::with([
                'purchaseOrder.supplier', 
                'purchaseOrder.items.item',
                'receivedBy',
                'createdBy',
                'modifiedBy'
            ])->findOrFail($id);

            // Process item statuses
            $itemStatuses = json_decode($receiving->ItemStatuses, true) ?? [];
            foreach ($receiving->purchaseOrder->items as $item) {
                $status = $itemStatuses[$item->ItemId] ?? ['status' => 'Pending', 'received_qty' => 0];
                $item->status = $status['status'] ?? 'Pending';
                $item->received_qty = $status['received_qty'] ?? 0;
            }

            return view('receiving.show', compact('receiving'));
        } catch (\Exception $e) {
            Log::error('Error showing receiving: ' . $e->getMessage());
            return back()->with('error', 'Error showing receiving: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Starting delete process for receiving record: ' . $id);
            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $receiving = Receiving::with('purchaseOrder')
                ->where('IsDeleted', false)
                ->findOrFail($id);

            if ($receiving->IsDeleted) {
                throw new \Exception('Record is already deleted.');
            }

            $receiving->softDelete($currentEmployee->EmployeeID);

            DB::commit();
            Log::info('Receiving record deleted successfully');
            return response()->json([
                'success' => true,
                'message' => 'Receiving deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting receiving: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting receiving: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            Log::info('Starting restore process for receiving record: ' . $id);
            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $receivingRecord = Receiving::where('IsDeleted', true)->findOrFail($id);

            $receivingRecord->softRestore($currentEmployee->EmployeeID);

            DB::commit();
            Log::info('Receiving record restored successfully');
            return response()->json(['success' => true, 'message' => 'Receiving record restored successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring receiving record: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error restoring receiving record: ' . $e->getMessage()], 500);
        }
    }

    private function validateStockQuantities($purchaseOrder)
    {
        foreach ($purchaseOrder->items as $poItem) {
            if ($poItem->Quantity <= 0) {
                throw new \Exception("Invalid quantity for item: {$poItem->item->ItemName}");
            }
        }
    }
} 