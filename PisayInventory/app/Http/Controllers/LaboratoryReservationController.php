<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\LaboratoryReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

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

        $userRole = DB::table('useraccount')
            ->where('UserAccountID', Auth::id())
            ->value('role');

        // Strict filtering is handled by AJAX endpoint, so don't pass reservations here
        $laboratories = Laboratory::where('status', 'Available')
            ->orderBy('laboratory_name')
            ->get();

        return view('laboratory.reservations.index', compact(
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
            // Log the raw request data
            \Log::info('Raw reservation request data:', [
                'all' => $request->all(),
                'has_reservation_date_to' => $request->has('reservation_date_to'),
                'reservation_date_to' => $request->input('reservation_date_to'),
                'reservation_date_from' => $request->input('reservation_date_from')
            ]);

            $user = auth()->user();
            $isTeacher = $user->role === 'Teacher';

            // Get employee ID if user is a teacher
            $employeeId = null;
            if ($isTeacher) {
                $employee = \App\Models\Employee::where('UserAccountID', $user->UserAccountID)->first();
                $employeeId = $employee ? $employee->EmployeeID : null;
            }

            // Modify validation rules based on user role
            $validationRules = [
                'laboratory_id' => 'required|exists:laboratories,laboratory_id',
                'campus' => 'required|string',
                'school_year' => 'required|string',
                'subject' => 'required|string',
                'reservation_date_from' => 'required|date|after:today',
                'reservation_date_to' => 'required|date|after_or_equal:reservation_date_from',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'num_students' => 'required|integer|min:1',
                'group_members' => 'nullable|array'
            ];

            // Add teacher_id validation only for students
            if (!$isTeacher) {
                $validationRules['teacher_id'] = 'required|exists:employee,EmployeeID';
            }

            // Modify grade_section validation based on role
            if ($isTeacher) {
                $validationRules['grade_section'] = 'nullable|string';
            } else {
                $validationRules['grade_section'] = 'required|string';
            }

            $validated = $request->validate($validationRules);

            // Log validated data
            \Log::info('Validated reservation data:', $validated);

            // Your existing conflict check (move this before DB::beginTransaction and creation)
            $conflictingReservation = LaboratoryReservation::where('laboratory_id', $validated['laboratory_id'])
                ->where(function($query) use ($validated) {
                    $query->where(function($q) use ($validated) {
                        $q->where('reservation_date_from', '<=', $validated['reservation_date_to'])
                          ->where('reservation_date_to', '>=', $validated['reservation_date_from']);
                    });
                })
                ->where('status', '!=', 'Cancelled')
                ->first();

            if ($conflictingReservation) {
                $conflictMsg = 'This time slot is already reserved';
                $conflictMsg .= ' from ' . $conflictingReservation->reservation_date_from . ' to ' . $conflictingReservation->reservation_date_to;
                $conflictMsg .= ' (' . $conflictingReservation->start_time . ' - ' . $conflictingReservation->end_time . ')';
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $conflictMsg
                    ], 409);
                }
                return back()->with('error', $conflictMsg)->withInput();
            }

            DB::beginTransaction();

            // Modified control number generation with laboratory type
            $timestamp = now();
            $dateComponent = $timestamp->format('Ymd');
            
            // Get laboratory type
            $laboratory = Laboratory::find($validated['laboratory_id']);
            
            // Map laboratory names to their types
            $labTypeMap = [
                'Microbiology Lab' => 'BIO',
                'Research Lab(Bio)' => 'BIO',
                'Botany(Bio) Lab' => 'BIO',
                'Zoology(Bio) Lab' => 'BIO',
                'Chemistry Lab 331' => 'CHEM',
                'Chemistry Lab 332' => 'CHEM',
                'Research Room 1/ 334' => 'CHEM',
                'Research Room 2/ 335' => 'CHEM',
                'Computer Lab 1' => 'COMLAB',
                'Computer Lab 2' => 'COMLAB'
            ];
            
            // Get laboratory type or default to 'GEN'
            $labType = $laboratory ? ($labTypeMap[$laboratory->laboratory_name] ?? 'GEN') : 'GEN';
            
            // Get the last control number for this lab type and date
            $prefix = "LR-{$labType}-{$dateComponent}";
            $lastReservation = LaboratoryReservation::where('control_no', 'like', $prefix . '%')
                ->orderBy('control_no', 'desc')
                ->first();
            
            // Generate the next sequence number
            $sequence = '001';
            if ($lastReservation) {
                $lastSequence = substr($lastReservation->control_no, -3);
                if (is_numeric($lastSequence)) {
                    $sequence = str_pad((int)$lastSequence + 1, 3, '0', STR_PAD_LEFT);
                }
            }
            
            // Format: LR-[LABTYPE]-[DATE]-[SEQUENCE]
            $controlNo = "{$prefix}-{$sequence}";

            // Set requested_by based on user type
            $requestedBy = '';
            if ($isTeacher) {
                $employee = \App\Models\Employee::where('UserAccountID', $user->UserAccountID)->first();
                $requestedBy = $employee ? $employee->FirstName . ' ' . $employee->LastName : $user->name;
            } else {
                $student = \App\Models\Student::where('UserAccountID', $user->UserAccountID)->first();
                $requestedBy = $student ? $student->first_name . ' ' . $student->last_name : $user->name;
            }

            // Set initial status based on user role
            $initialStatus = $isTeacher ? 'Approval of SRA / SRS' : 'Approval of Teacher';

            // Format the date range
            $reservation_date = $validated['reservation_date_from'] . ' to ' . $validated['reservation_date_to'];
            
            // Log the date value
            \Log::info('Reservation date range:', [
                'from' => $validated['reservation_date_from'],
                'to' => $validated['reservation_date_to'],
            ]);

            // Prepare creation data with role-specific values
            $creationData = [
                'reservation_id' => 'RES' . date('YmdHis'),
                'control_no' => $controlNo,
                'laboratory_id' => $validated['laboratory_id'],
                'reserver_id' => Auth::user()->UserAccountID,
                'campus' => $validated['campus'],
                'school_year' => $validated['school_year'],
                'grade_section' => $isTeacher ? 'N/A' : $validated['grade_section'],
                'subject' => $validated['subject'],
                'teacher_id' => $isTeacher ? $employeeId : $validated['teacher_id'],
                'reservation_date_from' => $validated['reservation_date_from'],
                'reservation_date_to' => $validated['reservation_date_to'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'num_students' => $validated['num_students'],
                'requested_by' => $requestedBy,
                'requested_by_type' => $isTeacher ? 'teacher' : 'student',
                'endorsement_status' => 'For Endorsement',
                'endorser_role' => $isTeacher ? 'Unit Head' : 'Teacher In-Charge',
                'date_requested' => now(),
                'group_members' => $validated['group_members'],
                'status' => $initialStatus,
                'remarks' => '',
                'created_by' => Auth::user()->UserAccountID,
                'IsDeleted' => false
            ];

            // Log the final creation data
            \Log::info('Final creation data:', $creationData);

            $reservation = LaboratoryReservation::create($creationData);

            // Log the created reservation
            \Log::info('Created reservation:', $reservation->toArray());

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
            \Log::error('Error creating reservation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
                'reservation_date_from' => 'required|date|after:today',
                'reservation_date_to' => 'nullable|date|after_or_equal:reservation_date_from',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'num_students' => 'required|integer|min:1',
                'group_members' => 'nullable|array',
                'remarks' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Modified control number generation with laboratory type
            $timestamp = now();
            $dateComponent = $timestamp->format('Ymd');
            
            // Get laboratory type
            $laboratory = Laboratory::find($validated['laboratory_id']);
            
            // Map laboratory names to their types
            $labTypeMap = [
                'Microbiology Lab' => 'BIO',
                'Research Lab(Bio)' => 'BIO',
                'Botany(Bio) Lab' => 'BIO',
                'Zoology(Bio) Lab' => 'BIO',
                'Chemistry Lab 331' => 'CHEM',
                'Chemistry Lab 332' => 'CHEM',
                'Research Room 1/ 334' => 'CHEM',
                'Research Room 2/ 335' => 'CHEM',
                'Computer Lab 1' => 'COMLAB',
                'Computer Lab 2' => 'COMLAB'
            ];
            
            // Get laboratory type or default to 'GEN'
            $labType = $laboratory ? ($labTypeMap[$laboratory->laboratory_name] ?? 'GEN') : 'GEN';
            
            // Get the last control number for this lab type and date
            $prefix = "LR-{$labType}-{$dateComponent}";
            $lastReservation = LaboratoryReservation::where('control_no', 'like', $prefix . '%')
                ->orderBy('control_no', 'desc')
                ->first();
            
            // Generate the next sequence number
            $sequence = '001';
            if ($lastReservation) {
                $lastSequence = substr($lastReservation->control_no, -3);
                if (is_numeric($lastSequence)) {
                    $sequence = str_pad((int)$lastSequence + 1, 3, '0', STR_PAD_LEFT);
                }
            }
            
            // Format: LR-[LABTYPE]-[DATE]-[SEQUENCE]
            $controlNo = "{$prefix}-{$sequence}";

            // Check for conflicts
            $conflictingReservation = LaboratoryReservation::where('laboratory_id', $validated['laboratory_id'])
                ->where('reservation_date_from', $validated['reservation_date_from'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
                })
                ->where('status', '!=', 'Cancelled')
                ->first();

            if ($conflictingReservation) {
                throw new \Exception('This time slot is already reserved.');
            }

            // Debug: log the incoming request data
            \Log::info('Reservation request data (studentStore):', $request->all());

            $reservation_date_from = $validated['reservation_date_from'];
            $reservation_date_to = $request->input('reservation_date_to');
            if (!$reservation_date_to || $reservation_date_to === 'null' || $reservation_date_to === '') {
                $reservation_date_to = $reservation_date_from;
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
                'reservation_date_from' => $reservation_date_from,
                'reservation_date_to' => $reservation_date_to,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'num_students' => $validated['num_students'],
                'requested_by_type' => 'student',
                'requested_by' => Auth::user()->student ? Auth::user()->student->first_name . ' ' . Auth::user()->student->last_name : Auth::user()->name,
                'date_requested' => now(),
                'group_members' => $validated['group_members'],
                'status' => 'Approval of Teacher',
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
    public function getTeachers(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $teachers = DB::table('employee')
                ->join('employee_roles', 'employee.EmployeeID', '=', 'employee_roles.EmployeeId')
                ->join('roles', 'employee_roles.RoleId', '=', 'roles.RoleId')
                ->select(
                    'employee.EmployeeID as id',
                    DB::raw("CONCAT(employee.FirstName, ' ', employee.LastName) as text")
                )
                ->where('employee.IsDeleted', 0)
                ->where('employee_roles.IsDeleted', 0)
                ->where('roles.RoleName', 'Teacher')
                ->where(function($query) use ($search) {
                    $query->where('employee.FirstName', 'LIKE', "%{$search}%")
                        ->orWhere('employee.LastName', 'LIKE', "%{$search}%");
                })
                ->distinct()
                ->get();

            return response()->json($teachers);

        } catch (\Exception $e) {
            \Log::error('Error in getTeachers:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to fetch teachers: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getStudentInfo()
    {
        try {
            $user = Auth::user();
            $student = \App\Models\Student::where('UserAccountID', $user->UserAccountID)->first();
            
            if ($student) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'isStudent' => true,
                        'isTeacher' => false,
                        'full_name' => trim($student->FirstName . ' ' . $student->LastName),
                        'grade_level' => $student->grade_level,
                        'section' => $student->section,
                        'campus' => $student->campus ?? 'Main'
                    ]
                ]);
            } 
            
            $employee = \App\Models\Employee::where('UserAccountID', $user->UserAccountID)->first();
            if ($employee) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'isStudent' => false,
                        'isTeacher' => true,
                        'full_name' => trim($employee->FirstName . ' ' . $employee->LastName),
                        'campus' => $employee->campus ?? 'Main'
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No user information found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error in getStudentInfo:', [
                'error' => $e->getMessage(),
                'user' => Auth::user()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user information: ' . $e->getMessage()
            ], 500);
        }
    }

    public function endorse($id)
    {
        try {
            \Log::info('Endorsement request received for ID: ' . $id);
            
            // Find the reservation
            $reservation = LaboratoryReservation::where('reservation_id', 'RES' . $id)
                ->orWhere('reservation_id', $id)
                ->firstOrFail();
                
            $user = auth()->user();
            
            // Get the employee record associated with the user
            $employee = \App\Models\Employee::where('UserAccountID', $user->UserAccountID)->first();
            
            if (!$employee) {
                \Log::error('No employee record found for user:', ['user_id' => $user->UserAccountID]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 400);
            }

            // Debug logging for authorization check
            \Log::info('Authorization check:', [
                'user_employee_id' => $employee->EmployeeID,
                'teacher_id' => $reservation->teacher_id,
                'is_teacher_match' => $employee->EmployeeID === $reservation->teacher_id,
                'requested_by_type' => $reservation->requested_by_type,
                'user_role' => $user->role
            ]);

            // Check if user is the Teacher In-Charge for student requests
            $isTeacherInCharge = $employee->EmployeeID === $reservation->teacher_id;
            
            // Validate if user can endorse
            if (($isTeacherInCharge && $reservation->requested_by_type === 'student') ||
                ($user->role === 'Unit Head' && $reservation->requested_by_type === 'teacher')) {
                
                DB::beginTransaction();
                
                try {
                    $reservation->update([
                        'status' => 'Approved',
                        'endorsement_status' => 'Approved',
                        'endorsed_by' => $employee->EmployeeID,
                        'endorsed_at' => now(),
                        'endorser_role' => $isTeacherInCharge ? 'Teacher In-Charge' : 'Unit Head',
                        'approved_by' => $employee->EmployeeID,
                        'approved_at' => now(),
                        'updated_by' => $employee->EmployeeID
                    ]);

                    DB::commit();

                    \Log::info('Reservation endorsed successfully', [
                        'reservation_id' => $reservation->reservation_id,
                        'endorsed_by' => $employee->EmployeeID
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Reservation has been approved'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            \Log::warning('Authorization failed:', [
                'is_teacher_in_charge' => $isTeacherInCharge,
                'requested_by_type' => $reservation->requested_by_type,
                'user_role' => $user->role,
                'condition1' => ($isTeacherInCharge && $reservation->requested_by_type === 'student'),
                'condition2' => ($user->role === 'Unit Head' && $reservation->requested_by_type === 'teacher')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to endorse this reservation'
            ], 403);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Endorsement error:', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add method to approve reservation
    public function approve($id)
    {
        try {
            // Get the full ID from the request
            $fullId = request('full_id', $id);
            $reservation = LaboratoryReservation::where('reservation_id', $fullId)->firstOrFail();
            
            // Get the current status from the request
            $currentStatus = request('current_status');
            
            // Get the current user
            $user = Auth::user();
            $isTeacher = $user->role === 'Teacher';
            // Update: Allow SRS-BIO, SRS-CHEM, SRS-COMLAB as SRA/SRS
            $isSRAorSRS = in_array($user->role, ['SRA', 'SRS', 'SRS-BIO', 'SRS-CHEM', 'SRS-COMLAB']);

            // Get the employee ID associated with the current user
            $employee = \App\Models\Employee::where('UserAccountID', Auth::user()->UserAccountID)->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employee record found for the current user.'
                ], 422);
            }

            // Add logging to track the update
            \Log::info('Attempting to approve reservation', [
                'reservation_id' => $fullId,
                'current_status' => $currentStatus,
                'employee_id' => $employee->EmployeeID,
                'user_role' => $user->role
            ]);

            // Determine the new status based on current status and user role
            $newStatus = '';
            if ($currentStatus === 'Approval of Teacher' && $isTeacher) {
                $newStatus = 'Approval of SRA / SRS';
            } else if ($currentStatus === 'Approval of SRA / SRS' && $isSRAorSRS) {
                $newStatus = 'Approved';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition or unauthorized action.'
                ], 403);
            }

            // Update the reservation
            $reservation->update([
                'status' => $newStatus,
                'approved_by' => $employee->EmployeeID,
                'approved_at' => now(),
                'updated_by' => $employee->EmployeeID
            ]);

            // Verify the update
            $updatedReservation = $reservation->fresh();
            \Log::info('Reservation approved', [
                'status' => $updatedReservation->status,
                'approved_by' => $updatedReservation->approved_by,
                'approved_at' => $updatedReservation->approved_at
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

    public function disapprove($id)
    {
        try {
            // Get the full ID from the request
            $fullId = request('full_id', $id);
            $reservation = LaboratoryReservation::where('reservation_id', $fullId)->firstOrFail();
            
            // Enhanced debugging for initial state
            \Log::info('Disapproval request received', [
                'id' => $fullId,
                'remarks' => request('remarks'),
                'request_data' => request()->all(),
                'current_status' => $reservation->status,
                'current_disapproved_by' => $reservation->disapproved_by
            ]);
            
            if ($reservation->status !== 'For Approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only reservations with For Approval status can be disapproved.'
                ], 422);
            }

            $employee = \App\Models\Employee::where('UserAccountID', Auth::user()->UserAccountID)->first();
            
            // Log employee details
            \Log::info('Employee found', [
                'employee_id' => $employee ? $employee->EmployeeID : null,
                'name' => $employee ? ($employee->FirstName . ' ' . $employee->LastName) : 'Not found'
            ]);
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employee record found for the current user.'
                ], 422);
            }

            DB::beginTransaction();

            try {
                $updateData = [
                    'status' => 'Disapproved',
                    'disapproved_by' => $employee->EmployeeID,
                    'disapproved_at' => now(),
                    'remarks' => request('remarks'),
                    'updated_by' => $employee->EmployeeID
                ];

                $reservation->update($updateData);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Reservation disapproved successfully.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error disapproving reservation: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getReservationsData(Request $request)
    {
        try {
            $status = $request->get('status', 'For Approval');
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);

            // Get user role and trim any whitespace
            $userRole = trim(DB::table('useraccount')
                ->where('UserAccountID', Auth::id())
                ->value('role'));

            // Debug log user role
            Log::info('User role for filtering:', ['role' => $userRole]);

            $query = LaboratoryReservation::with(['laboratory', 'teacher', 'reserver'])
                ->whereNull('deleted_at');

            // Strict role-based filtering (except Admin and Teacher)
            if ($userRole && $userRole !== 'Admin' && !str_contains($userRole, 'Teacher')) {
                $labIds = Laboratory::where('role', $userRole)->pluck('laboratory_id')->toArray();
                // Debug log filtered lab IDs
                Log::info('Filtered laboratory IDs:', [
                    'userRole' => $userRole,
                    'labIds' => $labIds
                ]);
                $query->whereIn('laboratory_id', $labIds);
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('control_no', 'like', "%{$search}%")
                        ->orWhere('grade_section', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('requested_by', 'like', "%{$search}%")
                        ->orWhereHas('laboratory', function($q) use ($search) {
                            $q->where('laboratory_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('teacher', function($q) use ($search) {
                            $q->where('FirstName', 'like', "%{$search}%")
                              ->orWhere('LastName', 'like', "%{$search}%");
                        });
                });
            }

            $reservations = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Additional PHP-side filtering as a safety measure
            $data = collect($reservations->items())->filter(function($reservation) use ($userRole) {
                if ($userRole === 'Admin' || str_contains($userRole, 'Teacher')) return true;
                return $reservation->laboratory && trim($reservation->laboratory->role) === $userRole;
            })->map(function($reservation) {
                $arr = $reservation->toArray();
                $arr['requested_by_role'] = $reservation->requested_by_type ?? null;
                $arr['reservation_date_to'] = $reservation->reservation_date_to ?? null;
                return $arr;
            });

            // Debug log final filtered data
            Log::info('Final filtered reservations:', [
                'count' => $data->count(),
                'userRole' => $userRole
            ]);

            return response()->json([
                'data' => $data,
                'meta' => [
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                    'per_page' => $reservations->perPage(),
                    'total' => $reservations->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getReservationsData:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error loading reservations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatusCounts()
    {
        $counts = [
            'teacherApproval' => LaboratoryReservation::where('status', 'Approval of Teacher')->count(),
            'sraSrsApproval' => LaboratoryReservation::where('status', 'Approval of SRA / SRS')->count(),
            'approved' => LaboratoryReservation::where('status', 'Approved')->count(),
            'cancelled' => LaboratoryReservation::where('status', 'Cancelled')->count(),
            'disapproved' => LaboratoryReservation::where('status', 'Disapproved')->count()
        ];
        return response()->json($counts);
    }

    public function generateControlNo(Request $request)
    {
        $timestamp = now();
        $dateComponent = $timestamp->format('Ymd');
        
        // Get laboratory type from request
        $laboratoryId = $request->laboratory_id;
        $laboratory = Laboratory::find($laboratoryId);
        
        // Map laboratory names to their types
        $labTypeMap = [
            'Microbiology Lab' => 'BIO',
            'Research Lab(Bio)' => 'BIO',
            'Botany(Bio) Lab' => 'BIO',
            'Zoology(Bio) Lab' => 'BIO',
            'Chemistry Lab 331' => 'CHEM',
            'Chemistry Lab 332' => 'CHEM',
            'Research Room 1/ 334' => 'CHEM',
            'Research Room 2/ 335' => 'CHEM',
            'Computer Lab 1' => 'COMLAB',
            'Computer Lab 2' => 'COMLAB'
        ];
        
        // Get laboratory type or default to 'GEN'
        $labType = $laboratory ? ($labTypeMap[$laboratory->laboratory_name] ?? 'GEN') : 'GEN';
        
        // Get the last control number for this lab type and date
        $prefix = "LR-{$labType}-{$dateComponent}";
        $lastReservation = LaboratoryReservation::where('control_no', 'like', $prefix . '%')
            ->orderBy('control_no', 'desc')
            ->first();
        
        // Generate the next sequence number
        $sequence = '001';
        if ($lastReservation) {
            $lastSequence = substr($lastReservation->control_no, -3);
            if (is_numeric($lastSequence)) {
                $sequence = str_pad((int)$lastSequence + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        // Format: LR-[LABTYPE]-[DATE]-[SEQUENCE]
        $controlNo = "{$prefix}-{$sequence}";
        
        return response()->json([
            'control_no' => $controlNo
        ]);
    }

    /**
     * Display the specified reservation.
     *
     * @param  string  $id
    
     */
    public function show($id)
    {
        $reservation = LaboratoryReservation::with(['laboratory', 'teacher', 'endorser', 'approver'])->findOrFail($id);
        $readonly = true;
        if (request()->ajax()) {
            return view('laboratory.reservations._form', compact('reservation', 'readonly'))->render();
        }
        return view('laboratory.reservations._form', compact('reservation', 'readonly'));
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
            
            // Add logging to track the deletion attempt
            \Log::info('Attempting to delete reservation:', [
                'id' => $id,
                'status' => $reservation->status,
                'user' => auth()->user()->id
            ]);

            // Delete the reservation
            $reservation->delete();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reservation deleted successfully.'
                ]);
            }

            return redirect()->route('laboratory.reservations')
                ->with('success', 'Reservation deleted successfully.');
            
        } catch (\Exception $e) {
            \Log::error('Error deleting reservation:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting reservation: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting reservation: ' . $e->getMessage());
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

    /**
     * Get the data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $status = $request->get('status', 'For Approval');
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = LaboratoryReservation::with(['laboratory', 'reserver.employee'])
            ->where('status', $status)
            ->whereNull('deleted_at');

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

        $reservations = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Map reservations to include requested_by_role and reservation_date_to
        $data = collect($reservations->items())->map(function($reservation) {
            $arr = $reservation->toArray();
            $arr['requested_by_role'] = $reservation->requested_by_type ?? null;
            $arr['reservation_date_to'] = $reservation->reservation_date_to ?? null;
            return $arr;
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total()
            ]
        ]);
    }

} 