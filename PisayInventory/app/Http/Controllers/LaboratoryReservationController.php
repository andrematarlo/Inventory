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
    
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,laboratory_id',
            'campus' => 'required|string',
            'school_year' => 'required|string',
            'grade_section' => 'required|string',
            'subject' => 'required|string',
            'teacher_id' => 'required|exists:employee,EmployeeID',
            'reservation_date' => 'required|date|after:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'num_students' => 'required|integer|min:1',
            'requested_by_type' => 'required|in:teacher,student',
            'requested_by' => 'required|string',
            'group_members' => 'nullable|array',
            'remarks' => 'nullable|string'
        ]);

        DB::beginTransaction();

        // Generate control number using stored procedure
        $controlNo = DB::select('CALL GenerateLabReservationControlNo(@control_no)');
        $result = DB::select('SELECT @control_no as control_no');
        $controlNo = $result[0]->control_no;

        // Check for conflicting reservations
        $conflictingReservation = LaboratoryReservation::where('laboratory_id', $validated['laboratory_id'])
            ->where('reservation_date', $validated['reservation_date'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->where('status', '!=', 'Cancelled')
            ->first();

        if ($conflictingReservation) {
            throw new \Exception('This time slot is already reserved.');
        }

        $reservation = LaboratoryReservation::create([
            'reservation_id' => 'RES' . date('YmdHis'),
            'control_no' => $controlNo,
            'laboratory_id' => $validated['laboratory_id'],
            'reserver_id' => Auth::user()->UserAccountID,
            'campus' => $validated['campus'],
            'school_year' => $validated['school_year'],
            'grade_section' => $validated['grade_section'],
            'subject' => $validated['subject'],
            'teacher_id' => $validated['teacher_id'],
            'reservation_date' => $validated['reservation_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'num_students' => $validated['num_students'],
            'requested_by_type' => $validated['requested_by_type'],
            'requested_by' => $validated['requested_by'],
            'date_requested' => now(),
            'group_members' => $validated['group_members'],
            'status' => 'For Approval',
            'remarks' => $validated['remarks'],
            'created_by' => Auth::user()->UserAccountID,
            'IsDeleted' => false
        ]);

        DB::commit();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laboratory reservation created successfully.',
                'control_no' => $controlNo
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
 * Show the form for student reservation.
 */
public function studentCreate()
{
    // Get the authenticated user
    $user = auth()->user();
    
    // Get list of laboratories
    $laboratories = Laboratory::where('IsDeleted', false)->get();
    
    // Simply return the view with laboratories
    return view('laboratory.reservations.reserve', compact('laboratories'));
}

/**
 * Store a student reservation.
 */
public function studentStore(Request $request)
{
    try {
        if (!Auth::user()->hasRole('Student')) {
            return response()->json([
                'success' => false,
                'message' => 'Only students can submit this form.'
            ], 403);
        }

        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,laboratory_id',
            'campus' => 'required|string',
            'school_year' => 'required|string',
            'grade_section' => 'required|string',
            'subject' => 'required|string',
            'teacher_id' => 'required|exists:employee,EmployeeID',
            'reservation_date' => 'required|date|after:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'num_students' => 'required|integer|min:1',
            'group_members' => 'nullable|array',
            'remarks' => 'nullable|string'
        ]);

        DB::beginTransaction();

        // Generate control number
        $controlNo = DB::select('CALL GenerateLabReservationControlNo(@control_no)');
        $result = DB::select('SELECT @control_no as control_no');
        $controlNo = $result[0]->control_no;

        // Check for conflicts
        $conflictingReservation = LaboratoryReservation::where('laboratory_id', $validated['laboratory_id'])
            ->where('reservation_date', $validated['reservation_date'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->where('status', '!=', 'Cancelled')
            ->first();

        if ($conflictingReservation) {
            throw new \Exception('This time slot is already reserved.');
        }

        $reservation = LaboratoryReservation::create([
            'reservation_id' => 'RES' . date('YmdHis'),
            'control_no' => $controlNo,
            'laboratory_id' => $validated['laboratory_id'],
            'reserver_id' => Auth::user()->UserAccountID,
            'campus' => $validated['campus'],
            'school_year' => $validated['school_year'],
            'grade_section' => $validated['grade_section'],
            'subject' => $validated['subject'],
            'teacher_id' => $validated['teacher_id'],
            'reservation_date' => $validated['reservation_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'num_students' => $validated['num_students'],
            'requested_by_type' => 'student',
            'requested_by' => Auth::user()->student->FirstName . ' ' . Auth::user()->student->LastName,
            'date_requested' => now(),
            'group_members' => $validated['group_members'],
            'status' => 'For Approval',
            'remarks' => $validated['remarks'],
            'created_by' => Auth::user()->UserAccountID,
            'IsDeleted' => false
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Your reservation has been submitted successfully.',
            'control_no' => $controlNo
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error creating reservation: ' . $e->getMessage()
        ], 500);
    }
}
    

    // Add new method to get teachers
public function getTeachers()
{
    $teachers = DB::table('employee')
        ->join('useraccount_roles', 'employee.UserAccountID', '=', 'useraccount_roles.UserAccountID')
        ->join('roles', 'useraccount_roles.RoleId', '=', 'roles.RoleId')
        ->where('roles.RoleName', 'Teacher')
        ->select('employee.EmployeeID', 
                DB::raw("CONCAT(employee.FirstName, ' ', employee.LastName) as full_name"))
        ->get();

    return response()->json($teachers);
}

// Add method to approve reservation
public function approve($id)
{
    try {
        $reservation = LaboratoryReservation::findOrFail($id);
        
        if ($reservation->status !== 'For Approval') {
            return response()->json([
                'success' => false,
                'message' => 'Only reservations with For Approval status can be approved.'
            ], 422);
        }

        $reservation->update([
            'status' => 'Approved',
            'approved_by' => Auth::user()->UserAccountID,
            'updated_by' => Auth::user()->UserAccountID
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservation approved successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error approving reservation: ' . $e->getMessage()
        ], 500);
    }
}


public function getReservationsData(Request $request)
{
    $status = $request->get('status', 'For Approval');
    $search = $request->get('search', '');
    $perPage = $request->get('per_page', 10);

    $query = LaboratoryReservation::with(['laboratory', 'teacher', 'reserver'])
        ->where('status', $status);

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('control_no', 'like', "%{$search}%")
                ->orWhere('grade_section', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhereHas('laboratory', function($q) use ($search) {
                    $q->where('laboratory_name', 'like', "%{$search}%");
                });
        });
    }

    $reservations = $query->orderBy('created_at', 'desc')->paginate($perPage);

    return response()->json($reservations);
}

public function getStatusCounts()
{
    $counts = [
        'For Approval' => LaboratoryReservation::where('status', 'For Approval')->count(),
        'Approved' => LaboratoryReservation::where('status', 'Approved')->count(),
        'Cancelled' => LaboratoryReservation::where('status', 'Cancelled')->count()
    ];

    return response()->json($counts);
}

public function generateControlNo()
{
    $controlNo = DB::select('CALL GenerateLabReservationControlNo(@control_no)');
    $result = DB::select('SELECT @control_no as control_no');
    
    return response()->json([
        'control_no' => $result[0]->control_no
    ]);
}

    /**
     * Display the specified reservation.
     *
     * @param  string  $id
    
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