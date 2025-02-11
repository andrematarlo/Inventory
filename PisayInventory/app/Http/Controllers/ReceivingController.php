<?php

namespace App\Http\Controllers;

use App\Models\Receiving;
use App\Models\PurchaseOrder;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceivingController extends Controller
{
    public function index()
    {
        try {
            $receivings = Receiving::with(['purchaseOrder.supplier', 'receivedBy'])
                ->where('IsDeleted', false)
                ->orderBy('DateReceived', 'desc')
                ->get();

            return view('receiving.index', compact('receivings'));
        } catch (\Exception $e) {
            Log::error('Error loading receivings: ' . $e->getMessage());
            return back()->with('error', 'Error loading receivings: ' . $e->getMessage());
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

            $purchaseOrder = PurchaseOrder::findOrFail($request->PurchaseOrderID);

            if ($purchaseOrder->Status !== 'Pending') {
                return back()->with('error', 'This purchase order has already been received.');
            }

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

            // Update item stocks
            foreach ($purchaseOrder->items as $item) {
                $item->item->increment('StocksAvailable', $item->Quantity);
            }

            DB::commit();
            return redirect()->route('receiving.index')
                ->with('success', 'Items received successfully');

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
            DB::beginTransaction();

            $currentEmployee = Employee::where('UserAccountID', Auth::user()->UserAccountID)
                ->where('IsDeleted', false)
                ->firstOrFail();

            $receiving = Receiving::findOrFail($id);

            if ($receiving->Status !== 'Pending') {
                return back()->with('error', 'Only pending receivings can be deleted.');
            }

            $receiving->update([
                'IsDeleted' => true,
                'DeletedByID' => $currentEmployee->EmployeeID,
                'DateDeleted' => now()
            ]);

            DB::commit();
            return redirect()->route('receiving.index')
                ->with('success', 'Receiving deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting receiving: ' . $e->getMessage());
            return back()->with('error', 'Error deleting receiving: ' . $e->getMessage());
        }
    }
} 