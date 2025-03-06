<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\LaboratoryReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LaboratoryReservationController extends Controller
{
    protected $moduleName = 'Laboratory Management';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the reservations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userPermissions = $this->getUserPermissions('Laboratory Reservations');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to view laboratory reservations.');
        }

        $activeReservations = LaboratoryReservation::with(['laboratory', 'reserver.employee'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $deletedReservations = LaboratoryReservation::with(['laboratory', 'reserver.employee'])
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        $laboratories = Laboratory::where('status', 'Available')
            ->orderBy('laboratory_name')
            ->get();

        return view('laboratory.reservations.index', compact(
            'activeReservations',
            'deletedReservations',
            'userPermissions',
            'laboratories'
        ));
    }

    /**
     * Show the form for creating a new reservation.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $userPermissions = $this->getUserPermissions('Laboratory Reservations');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('laboratory.reservations')->with('error', 'You do not have permission to create reservations.');
        }

        $laboratories = Laboratory::where('status', 'Available')->orderBy('laboratory_name')->get();
        return view('laboratory.reservations.create', compact('userPermissions', 'laboratories'));
    }

    /**
     * Store a newly created reservation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'laboratory_id' => 'required|exists:laboratories,laboratory_id',
                'reservation_date' => 'required|date|after:today',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'purpose' => 'required|string',
                'num_students' => 'nullable|integer|min:1',
                'remarks' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $reservation = LaboratoryReservation::create([
                'reservation_id' => 'RES' . date('YmdHis'),
                'laboratory_id' => $validated['laboratory_id'],
                'reserver_id' => Auth::user()->UserAccountID,
                'reservation_date' => $validated['reservation_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'purpose' => $validated['purpose'],
                'num_students' => $validated['num_students'],
                'status' => 'Active',
                'remarks' => $validated['remarks'],
                'created_by' => Auth::user()->UserAccountID,
                'IsDeleted' => false
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laboratory reservation created successfully.'
                ]);
            }

            return redirect()->route('laboratory.reservations')
                ->with('success', 'Laboratory reservation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating reservation: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error creating reservation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified reservation.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reservation = LaboratoryReservation::with(['laboratory', 'reserver.employee'])
            ->findOrFail($id);
        
        if (request()->ajax()) {
            return view('laboratory.reservations.show', compact('reservation'))->render();
        }
        
        return view('laboratory.reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified reservation.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $reservation = LaboratoryReservation::findOrFail($id);
        $laboratories = Laboratory::where('status', 'Available')
            ->orWhere('laboratory_id', $reservation->laboratory_id)
            ->orderBy('laboratory_name')
            ->get();

        if (request()->ajax()) {
            return view('laboratory.reservations.edit_form', compact('reservation', 'laboratories'))->render();
        }

        return view('laboratory.reservations.edit', compact('reservation', 'laboratories'));
    }

    /**
     * Update the specified reservation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $reservation = LaboratoryReservation::findOrFail($id);

            if (!$reservation->isActive() || !$reservation->isUpcoming()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only upcoming active reservations can be edited.'
                ], 422);
            }

            $validated = $request->validate([
                'laboratory_id' => 'required|exists:laboratories,laboratory_id',
                'reservation_date' => 'required|date|after:today',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'purpose' => 'required|string',
                'num_students' => 'nullable|integer|min:1',
                'remarks' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $reservation->update([
                'laboratory_id' => $validated['laboratory_id'],
                'reservation_date' => $validated['reservation_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'purpose' => $validated['purpose'],
                'num_students' => $validated['num_students'],
                'remarks' => $validated['remarks'],
                'updated_by' => Auth::user()->UserAccountID
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified reservation from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $reservation = LaboratoryReservation::findOrFail($id);
            
            if (!$reservation->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reservation cannot be cancelled.'
                ], 422);
            }

            DB::beginTransaction();
            
            $reservation->update([
                'status' => 'Cancelled',
                'deleted_by' => Auth::user()->UserAccountID
            ]);
            
            $reservation->delete(); // This will trigger soft delete
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the permissions for Laboratory Management module.
     *
     * @param string|null $module The module name to check permissions for
     * @return \App\Models\RolePolicy|object
     */
    public function getUserPermissions($module = null)
    {
        try {
            if (!Auth::check()) {
                return (object)[
                    'CanAdd' => false,
                    'CanEdit' => false,
                    'CanDelete' => false,
                    'CanView' => false
                ];
            }

            return parent::getUserPermissions($module ?? 'Laboratory Reservations');

        } catch (\Exception $e) {
            \Log::error('Error getting permissions: ' . $e->getMessage());
            return (object)[
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'CanView' => false
            ];
        }
    }

    public function cancel(LaboratoryReservation $reservation)
    {
        if ($reservation->cancelled_at) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation is already cancelled.'
            ], 422);
        }

        if ($reservation->end_time < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a completed reservation.'
            ], 422);
        }

        $reservation->cancelled_at = now();
        $reservation->cancelled_by = Auth::id();
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Reservation cancelled successfully.'
        ]);
    }

    public function restore($id)
    {
        try {
            $reservation = LaboratoryReservation::withTrashed()->findOrFail($id);
            $reservation->restore();
            
            return response()->json([
                'success' => true,
                'message' => 'Reservation restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring reservation: ' . $e->getMessage()
            ], 500);
        }
    }
} 