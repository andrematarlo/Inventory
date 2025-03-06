<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBorrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EquipmentBorrowingController extends Controller
{
    protected $moduleName = 'Equipment Management';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the borrowings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::info('Accessing equipment borrowings index');
        
        $userPermissions = $this->getUserPermissions('Equipment Borrowings');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to view equipment borrowings.');
        }

        try {
            // Get available equipment for the create form
            $equipment = Equipment::where('status', 'Available')
                ->where('IsDeleted', false)  // Only get non-deleted equipment
                ->orderBy('equipment_name')
                ->get();

            // Get active and deleted borrowings
            $activeBorrowings = EquipmentBorrowing::with([
                'equipment',
                'borrower.employee',
                'creator.employee',
                'modifier.employee'
            ])
            ->where('IsDeleted', false)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

            $deletedBorrowings = EquipmentBorrowing::with([
                'equipment',
                'borrower.employee',
                'deleter.employee',
                'restorer.employee'
            ])
            ->where('IsDeleted', true)
            ->orderBy('deleted_at', 'desc')
            ->paginate(25);

            return view('equipment.borrowings.index', compact(
                'equipment',
                'activeBorrowings',
                'deletedBorrowings',
                'userPermissions'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading equipment borrowings: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Error loading equipment borrowings: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new borrowing.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check if user has permission to add borrowings
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('equipment.borrowings')->with('error', 'You do not have permission to borrow equipment.');
        }

        $equipment = Equipment::where('status', 'Available')->orderBy('equipment_name')->get();
        return view('equipment.borrowings.create', compact('userPermissions', 'equipment'));
    }

    /**
     * Store a newly created borrowing in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Check permissions
            $userPermissions = $this->getUserPermissions('Equipment Borrowings');
            if (!$userPermissions || !$userPermissions->CanAdd) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to borrow equipment.'
                ], 403);
            }

            $validated = $request->validate([
                'equipment_id' => 'required|exists:equipment,equipment_id',
                'borrow_date' => 'required|date|after_or_equal:today',
                'expected_return_date' => 'required|date|after:borrow_date',
                'purpose' => 'required|string',
                'condition_on_borrow' => 'required|string|in:Good,Fair,Poor',
                'remarks' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Check if equipment is available
            $equipment = Equipment::where('equipment_id', $validated['equipment_id'])
                ->where('status', 'Available')
                ->first();

            if (!$equipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipment is not available for borrowing.'
                ], 422);
            }

            $borrowing = EquipmentBorrowing::create([
                'borrowing_id' => 'BOR' . date('YmdHis'),
                'equipment_id' => $validated['equipment_id'],
                'borrower_id' => Auth::user()->UserAccountID,
                'borrow_date' => $validated['borrow_date'],
                'expected_return_date' => $validated['expected_return_date'],
                'purpose' => $validated['purpose'],
                'status' => 'Active',
                'condition_on_borrow' => $validated['condition_on_borrow'],
                'remarks' => $validated['remarks'],
                'created_by' => Auth::user()->UserAccountID,
                'IsDeleted' => false
            ]);

            // Update equipment status
            $equipment->update(['status' => 'Borrowed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Equipment borrowed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error borrowing equipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error borrowing equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified borrowing.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $borrowing = EquipmentBorrowing::with([
                'equipment',
                'borrower.employee',
                'creator.employee',
                'modifier.employee',
                'deleter.employee',
                'restorer.employee'
            ])->withTrashed()->findOrFail($id);
            
            return view('equipment.borrowings.show', compact('borrowing'));
        } catch (\Exception $e) {
            Log::error('Error showing borrowing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading borrowing details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified borrowing.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Check if user has permission to edit borrowings
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->route('equipment.borrowings')
                ->with('error', 'You do not have permission to edit borrowings.');
        }

        $borrowing = EquipmentBorrowing::findOrFail($id);
        
        // Only allow editing active borrowings
        if (!$borrowing->canBeReturned()) {
            return redirect()->route('equipment.borrowings')
                ->with('error', 'Only active borrowings can be edited.');
        }

        $equipment = Equipment::where('status', 'Available')
            ->orWhere('equipment_id', $borrowing->equipment_id)
            ->orderBy('equipment_name')
            ->get();
        
        return view('equipment.borrowings.edit', compact('borrowing', 'equipment', 'userPermissions'));
    }

    /**
     * Update the specified borrowing in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Check if user has permission to edit borrowings
        $userPermissions = $this->getUserPermissions('Laboratory Management');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->route('equipment.borrowings')
                ->with('error', 'You do not have permission to edit borrowings.');
        }

        $borrowing = EquipmentBorrowing::findOrFail($id);

        // Only allow editing active borrowings
        if (!$borrowing->canBeReturned()) {
            return redirect()->route('equipment.borrowings')
                ->with('error', 'Only active borrowings can be edited.');
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'expected_return_date' => 'required|date|after:borrow_date',
            'purpose' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update the borrowing
            $borrowing->update([
                'expected_return_date' => $request->expected_return_date,
                'purpose' => $request->purpose,
                'remarks' => $request->remarks,
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('equipment.borrowings')
                ->with('success', 'Borrowing updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating borrowing: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Process equipment return.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EquipmentBorrowing  $borrowing
     * @return \Illuminate\Http\Response
     */
    public function return(Request $request, EquipmentBorrowing $borrowing)
    {
        try {
            if ($borrowing->actual_return_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipment is already returned.'
                ], 422);
            }

            DB::beginTransaction();

            $borrowing->update([
                'actual_return_date' => now(),
                'status' => 'Returned',
                'updated_by' => Auth::user()->UserAccountID
            ]);

            // Update equipment status
            Equipment::where('equipment_id', $borrowing->equipment_id)
                ->update(['status' => 'Available']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Equipment returned successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error returning equipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error returning equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified borrowing from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $borrowing = EquipmentBorrowing::findOrFail($id);
            
            if ($borrowing->actual_return_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a returned borrowing.'
                ], 422);
            }

            DB::beginTransaction();
            
            // Update equipment status back to Available
            Equipment::where('equipment_id', $borrowing->equipment_id)
                ->update(['status' => 'Available']);
            
            $borrowing->update([
                'deleted_by' => Auth::user()->UserAccountID,
                'status' => 'Deleted'
            ]);
            
            $borrowing->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Borrowing record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting borrowing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting borrowing: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $borrowing = EquipmentBorrowing::withTrashed()->findOrFail($id);
            
            DB::beginTransaction();
            
            $borrowing->update([
                'restored_by' => Auth::user()->UserAccountID,
                'restored_at' => now(),
                'status' => $borrowing->actual_return_date ? 'Returned' : 'Active'
            ]);
            
            $borrowing->restore();
            
            // Update equipment status to Borrowed if the borrowing wasn't returned
            if (!$borrowing->actual_return_date) {
                Equipment::where('equipment_id', $borrowing->equipment_id)
                    ->update(['status' => 'Borrowed']);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Borrowing record restored successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring borrowing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error restoring borrowing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the permissions for Equipment Borrowings module.
     *
     * @param string $moduleName
     * @return \App\Models\RolePolicy|null
     */
    public function getUserPermissions($moduleName = 'Equipment Borrowings')
    {
        return parent::getUserPermissions($moduleName);
    }
} 