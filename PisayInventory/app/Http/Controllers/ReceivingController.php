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
            $receivingRecords = Receiving::with([
                'purchaseOrder.supplier', 
                'createdBy', 
                'modifiedBy',
                'purchaseOrder'
            ])
                ->where('IsDeleted', false)
                ->orderBy('DateReceived', 'desc')
                ->get();

            $deletedRecords = Receiving::with([
                'purchaseOrder.supplier', 
                'createdBy', 
                'modifiedBy',
                'deletedBy',
                'purchaseOrder'
            ])
                ->where('IsDeleted', true)
                ->get();

            return view('receiving.index', compact('receivingRecords', 'deletedRecords'));
        } catch (\Exception $e) {
            Log::error('Error loading receiving records: ' . $e->getMessage());
            return back()->with('error', 'Error loading receiving records');
        }
    }

    public function create()
    {
        try {
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
            $request->validate([
                'PurchaseOrderID' => 'required|exists:purchase_order,PurchaseOrderID',
                'Notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $purchaseOrder = PurchaseOrder::with(['items.item'])->findOrFail($request->PurchaseOrderID);

            if ($purchaseOrder->Status !== 'Pending') {
                return back()->with('error', 'This purchase order has already been received.');
            }

            $this->validateStockQuantities($purchaseOrder);

            // Create receiving record
            $receiving = Receiving::create([
                'PurchaseOrderID' => $request->PurchaseOrderID,
                'ReceivedByID' => $currentEmployee->EmployeeID,
                'DateReceived' => now(),
                'Status' => 'Received',
                'Notes' => $request->Notes,
                'DateCreated' => now(),
                'CreatedByID' => $currentEmployee->EmployeeID,
                'IsDeleted' => false
            ]);

            // Update PO status
            $purchaseOrder->update([
                'Status' => 'Received',
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            // Update inventory for each item
            foreach ($purchaseOrder->items as $poItem) {
                // Get the current item
                $item = $poItem->item;
                $newStockQuantity = $item->StocksAvailable + $poItem->Quantity;

                // Update item stocks
                $item->update([
                    'StocksAvailable' => $newStockQuantity,
                    'ModifiedByID' => $currentEmployee->EmployeeID,
                    'DateModified' => now()
                ]);

                // Create inventory record for stock movement
                $inventory = new Inventory();
                $inventory->ItemId = $item->ItemId;
                $inventory->ClassificationId = $item->ClassificationId;
                $inventory->StocksAdded = $poItem->Quantity;
                $inventory->StocksAvailable = $newStockQuantity;
                $inventory->DateCreated = now();
                $inventory->CreatedById = $currentEmployee->EmployeeID;
                $inventory->ModifiedById = $currentEmployee->EmployeeID;
                $inventory->DateModified = now();
                $inventory->IsDeleted = false;
                $inventory->save();

                // Create inventory movement record
                DB::table('inventory_movement')->insert([
                    'ItemID' => $item->ItemId,
                    'MovementType' => 'IN',
                    'Quantity' => $poItem->Quantity,
                    'ReferenceNumber' => $purchaseOrder->PONumber,
                    'ReferenceType' => 'Receiving',
                    'ReferenceID' => $receiving->ReceivingID,
                    'Notes' => "Received from PO: {$purchaseOrder->PONumber}",
                    'DateCreated' => now(),
                    'CreatedByID' => $currentEmployee->EmployeeID,
                    'IsDeleted' => false
                ]);
            }

            DB::commit();
            return redirect()->route('receiving.index')
                ->with('success', 'Items received and inventory updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error receiving items: ' . $e->getMessage());
            return back()->with('error', 'Error receiving items: ' . $e->getMessage());
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

            return view('receiving.show', compact('receiving'));
        } catch (\Exception $e) {
            Log::error('Error showing receiving: ' . $e->getMessage());
            return back()->with('error', 'Error showing receiving: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $receiving = Receiving::with(['purchaseOrder.supplier', 'purchaseOrder.items.item'])
                ->where('IsDeleted', false)
                ->findOrFail($id);

            if ($receiving->Status !== 'Pending') {
                return back()->with('error', 'Only pending receivings can be edited.');
            }

            return view('receiving.edit', compact('receiving'));
        } catch (\Exception $e) {
            Log::error('Error loading edit receiving form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'Notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $receiving = Receiving::findOrFail($id);

            if ($receiving->Status !== 'Pending') {
                return back()->with('error', 'Only pending receivings can be edited.');
            }

            $receiving->update([
                'Notes' => $request->Notes,
                'ModifiedByID' => $currentEmployee->EmployeeID,
                'DateModified' => now()
            ]);

            DB::commit();
            return redirect()->route('receiving.show', $id)
                ->with('success', 'Receiving updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating receiving: ' . $e->getMessage());
            return back()->with('error', 'Error updating receiving: ' . $e->getMessage());
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

            if ($receiving->Status !== 'Pending' || $receiving->purchaseOrder->Status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending receiving records with pending purchase orders can be deleted.'
                ], 400);
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