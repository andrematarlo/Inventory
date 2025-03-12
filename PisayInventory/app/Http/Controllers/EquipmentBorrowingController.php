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
    public function index(Request $request)
    {
        try {
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanView) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to view equipment borrowings.');
            }

            // Get available equipment for the create form
            $equipment = Equipment::where('status', 'Available')
                ->where('IsDeleted', false)
                ->orderBy('equipment_name')
                ->get();

            // Build the query
            $query = EquipmentBorrowing::with([
                'equipment',
                'borrower',
                'createdBy',
                'updatedBy',
                'deletedBy',
                'restoredBy'
            ]);

            // Handle search
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('borrowing_id', 'like', "%{$search}%")
                      ->orWhere('purpose', 'like', "%{$search}%")
                      ->orWhereHas('equipment', function($q) use ($search) {
                          $q->where('equipment_name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('borrower', function($q) use ($search) {
                          $q->where('FullName', 'like', "%{$search}%");
                      });
                });
            }

            // Handle trashed records
            if ($request->has('trashed')) {
                $query->withTrashed();
            }

            // Get per page value
            $perPage = $request->get('per_page', 10);

            // Get paginated results
            $borrowings = $query->orderBy('created_at', 'desc')
                               ->paginate($perPage)
                               ->appends($request->query());

            return view('equipment.borrowings.index', compact('borrowings', 'equipment', 'userPermissions'));

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
                'borrower',
                'createdBy',
                'updatedBy',
                'deletedBy',
                'restoredBy'
            ])->withTrashed()->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $borrowing
            ]);
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
        try {
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit borrowings.'
                ], 403);
            }

            $borrowing = EquipmentBorrowing::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $borrowing
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading borrowing details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading borrowing details'
            ], 500);
        }
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
        try {
            // Check permissions
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit borrowings.'
                ], 403);
            }

            // First validate the basic requirements
            $validator = Validator::make($request->all(), [
                'equipment_id' => 'required|exists:equipment,equipment_id',
                'borrow_date' => 'required|date',
                'expected_return_date' => 'required|date',
                'purpose' => 'required|string',
                'condition_on_borrow' => 'required|string|in:Good,Fair,Poor',
                'remarks' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Convert dates to Carbon instances for comparison
            $borrowDate = Carbon::parse($request->borrow_date)->startOfDay();
            $returnDate = Carbon::parse($request->expected_return_date)->startOfDay();

            // Validate that return date is after borrow date
            if ($returnDate->lte($borrowDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The expected return date must be after the borrow date.'
                ], 422);
            }

            DB::beginTransaction();

            $borrowing = EquipmentBorrowing::findOrFail($id);

            // Only allow editing active borrowings
            if (!$borrowing->canBeReturned()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active borrowings can be edited.'
                ], 422);
            }

            // Check if equipment is available or is the same as current
            if ($request->equipment_id !== $borrowing->equipment_id) {
                $equipment = Equipment::where('equipment_id', $request->equipment_id)
                    ->where(function($query) {
                        $query->where('status', 'Available')
                              ->orWhere('status', 'Good');
                    })
                    ->first();

                if (!$equipment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected equipment is not available.'
                    ], 422);
                }

                // Update old equipment status to Available
                Equipment::where('equipment_id', $borrowing->equipment_id)
                    ->update(['status' => 'Available']);

                // Update new equipment status to Borrowed
                $equipment->update(['status' => 'Borrowed']);
            }

            // Update borrowing record
            $borrowing->update([
                'equipment_id' => $request->equipment_id,
                'borrow_date' => $borrowDate->toDateString(),
                'expected_return_date' => $returnDate->toDateString(),
                'purpose' => $request->purpose,
                'condition_on_borrow' => $request->condition_on_borrow,
                'remarks' => $request->remarks,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Borrowing updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating borrowing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating borrowing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return the borrowed equipment.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function return($id, Request $request)
    {
        try {
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to return equipment.'
                ], 403);
            }

            $validated = $request->validate([
                'condition_on_return' => 'required|string|in:Good,Fair,Poor,Damaged',
                'remarks' => 'nullable|string'
            ]);

            $borrowing = EquipmentBorrowing::findOrFail($id);

            if ($borrowing->actual_return_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'This equipment has already been returned.'
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Update borrowing record
                $borrowing->update([
                    'actual_return_date' => now(),
                    'condition_on_return' => $validated['condition_on_return'],
                    'remarks' => $validated['remarks'],
                    'status' => 'Returned',
                    'updated_by' => Auth::id()
                ]);

                // Update equipment status
                $equipment = Equipment::findOrFail($borrowing->equipment_id);
                $equipment->update([
                    'status' => $validated['condition_on_return'] === 'Damaged' ? 'Damaged' : 'Available',
                    'condition' => $validated['condition_on_return']
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Equipment returned successfully.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error returning equipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error returning equipment'
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
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete borrowings.'
                ], 403);
            }

            $borrowing = EquipmentBorrowing::findOrFail($id);

            // Check if the borrowing can be deleted
            if ($borrowing->actual_return_date === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete an active borrowing. Please return the equipment first.'
                ], 422);
            }

            DB::beginTransaction();
            try {
                $borrowing->update([
                    'IsDeleted' => true,
                    'deleted_by' => Auth::id()
                ]);
                
                $borrowing->delete();
                
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Borrowing deleted successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error deleting borrowing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting borrowing'
            ], 500);
        }
    }

    /**
     * Restore the specified borrowing.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            $userPermissions = $this->getUserPermissions('Equipment');
            if (!$userPermissions || !$userPermissions->CanEdit) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to restore borrowings.'
                ], 403);
            }

            $borrowing = EquipmentBorrowing::withTrashed()->findOrFail($id);

            if (!$borrowing->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This borrowing is not deleted.'
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Update borrowing record
                $borrowing->update([
                    'IsDeleted' => false,
                    'RestoredById' => Auth::id(),
                    'DateRestored' => now(),
                    'status' => $borrowing->actual_return_date ? 'Returned' : 'Active'
                ]);

                // Restore the soft deleted record
                $borrowing->restore();

                // Update equipment status if borrowing is still active
                if (!$borrowing->actual_return_date) {
                    $equipment = Equipment::findOrFail($borrowing->equipment_id);
                    $equipment->update(['status' => 'In Use']);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Borrowing restored successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error restoring borrowing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error restoring borrowing'
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