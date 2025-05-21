<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaboratoryReagentController extends Controller
{
    public function index()
    {
        $requests = DB::table('laboratory_reagent_requests')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('laboratory.reagent.index', compact('requests'));
    }

    public function create()
    {
        // Generate next control number
        $year = now()->format('Y');
        $prefix = 'LAB-REG-' . $year . '-';
        
        $lastRecord = DB::table('laboratory_reagent_requests')
            ->where('control_no', 'like', $prefix . '%')
            ->orderBy('control_no', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->control_no, -5));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $nextControlNo = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        
        // Fetch all reagent items for dropdown
        $reagentItems = DB::table('laboratory_reagent_items')->orderBy('reagent')->get();

        $teachers = DB::table('employee')
            ->join('employee_roles', 'employee.EmployeeID', '=', 'employee_roles.EmployeeId')
            ->join('roles', 'employee_roles.RoleId', '=', 'roles.RoleId')
            ->where('employee.IsDeleted', 0)
            ->where('roles.RoleName', 'Teacher')
            ->orderBy('employee.LastName')
            ->select('employee.*')
            ->get();

        $srsChemUsers = DB::table('employee')
            ->join('employee_roles', 'employee.EmployeeID', '=', 'employee_roles.EmployeeId')
            ->join('roles', 'employee_roles.RoleId', '=', 'roles.RoleId')
            ->where('employee.IsDeleted', 0)
            ->where('roles.RoleName', 'SRS-CHEM')
            ->orderBy('employee.LastName')
            ->select('employee.*')
            ->get();

        return view('laboratory.reagent.create', compact('nextControlNo', 'reagentItems', 'teachers', 'srsChemUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_year' => 'required',
            'grade_section' => 'required',
            'number_of_students' => 'required|numeric',
            'subject' => 'required',
            'concurrent_topic' => 'required',
            'unit' => 'required',
            'teacher_in_charge' => 'required',
            'venue' => 'required',
            'inclusive_dates' => 'required',
            'inclusive_time_start' => 'required',
            'inclusive_time_end' => 'required',
            'quantities' => 'required|array',
            'reagents' => 'required|array',
            'received_by' => 'required',
            'date_received' => 'required|date',
            'endorsed_by' => 'required',
            'approved_by' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Generate control number
            $year = Carbon::now()->format('Y');
            $prefix = 'LAB-REG-' . $year . '-';
            
            $lastRecord = DB::table('laboratory_reagent_requests')
                ->where('control_no', 'like', $prefix . '%')
                ->orderBy('control_no', 'desc')
                ->first();

            if ($lastRecord) {
                $lastNumber = intval(substr($lastRecord->control_no, -5));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $controlNo = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            // Insert main record
            $requestId = DB::table('laboratory_reagent_requests')->insertGetId([
                'control_no' => $controlNo,
                'school_year' => $request->school_year,
                'grade_section' => $request->grade_section,
                'number_of_students' => $request->number_of_students,
                'subject' => $request->subject,
                'concurrent_topic' => $request->concurrent_topic,
                'unit' => $request->unit,
                'teacher_in_charge' => $request->teacher_in_charge,
                'venue' => $request->venue,
                'inclusive_dates' => $request->inclusive_dates,
                'inclusive_time_start' => $request->inclusive_time_start,
                'inclusive_time_end' => $request->inclusive_time_end,
                'student_names' => $request->student_names,
                'received_by' => $request->received_by,
                'date_received' => $request->date_received,
                'endorsed_by' => $request->endorsed_by,
                'approved_by' => $request->approved_by,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert reagent items
            foreach ($request->quantities as $key => $quantity) {
                if (!empty($request->reagents[$key])) {
                    DB::table('laboratory_reagent_items')->insert([
                        'request_id' => $requestId,
                        'quantity' => $quantity,
                        'reagent' => $request->reagents[$key],
                        'sds_checked' => isset($request->sds[$key]) ? true : false,
                        'issued_amount' => $request->issued_amounts[$key] ?? null,
                        'remarks' => $request->remarks[$key] ?? null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Reagent Request form has been submitted successfully. Control Number: ' . $controlNo);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while saving the form. Please try again. Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $reagentRequest = \App\Models\LaboratoryReagentRequest::where('request_id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$reagentRequest) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $items = DB::table('laboratory_reagent_items')
            ->where('request_id', $id)
            ->get();

        return view('laboratory.reagent.show', compact('reagentRequest', 'items'));
    }

    public function approve($id)
    {
        try {
            DB::table('laboratory_reagent_requests')
                ->where('request_id', $id)
                ->update([
                    'status' => 'approved',
                    'updated_at' => now()
                ]);

            return response()->json(['message' => 'Request approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error approving request'], 500);
        }
    }

    public function reject($id)
    {
        try {
            DB::table('laboratory_reagent_requests')
                ->where('request_id', $id)
                ->update([
                    'status' => 'rejected',
                    'updated_at' => now()
                ]);

            return response()->json(['message' => 'Request rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error rejecting request'], 500);
        }
    }

    public function delete($id)
    {
        try {
            DB::table('laboratory_reagent_requests')
                ->where('request_id', $id)
                ->update([
                    'deleted_at' => now()
                ]);

            return response()->json(['message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting record'], 500);
        }
    }
} 