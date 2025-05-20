<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaboratoryAccountabilityController extends Controller
{
    public function index()
    {
        $accountabilities = DB::table('laboratory_accountability')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('laboratory.accountability.index', compact('accountabilities'));
    }

    public function create()
    {
        // Generate next control number
        $year = now()->format('Y');
        $prefix = 'LAB-ACC-' . $year . '-';
        
        $lastRecord = DB::table('laboratory_accountability')
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
        
        $teachers = DB::table('employee')
            ->join('employee_roles', 'employee.EmployeeID', '=', 'employee_roles.EmployeeId')
            ->join('roles', 'employee_roles.RoleId', '=', 'roles.RoleId')
            ->where('employee.IsDeleted', 0)
            ->where('roles.RoleName', 'Teacher')
            ->orderBy('employee.LastName')
            ->select('employee.*')
            ->get();

        $accountabilityItems = DB::table('laboratory_accountability_items')
            ->orderBy('item')
            ->get();

        // Prepare item-description pairs for JS
        $itemDescriptions = [];
        foreach ($accountabilityItems as $item) {
            $itemDescriptions[$item->item] = $item->description;
        }

        return view('laboratory.accountability.create', compact('nextControlNo', 'teachers', 'accountabilityItems', 'itemDescriptions'));
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
            'inclusive_time' => 'required',
            'quantities' => 'required|array',
            'items' => 'required|array',
            'received_by' => 'required',
            'date_received' => 'required|date',
            'received_and_inspected_by' => 'required',
            'date_inspected' => 'required|date',
            'endorsed_by' => 'required',
            'approved_by' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Generate control number
            $year = Carbon::now()->format('Y');
            $prefix = 'LAB-ACC-' . $year . '-';
            
            $lastRecord = DB::table('laboratory_accountability')
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
            $accountabilityId = DB::table('laboratory_accountability')->insertGetId([
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
                'inclusive_time' => $request->inclusive_time,
                'student_names' => $request->student_names,
                'received_by' => $request->received_by,
                'date_received' => $request->date_received,
                'received_and_inspected_by' => $request->received_and_inspected_by,
                'date_inspected' => $request->date_inspected,
                'endorsed_by' => $request->endorsed_by,
                'approved_by' => $request->approved_by,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert items
            foreach ($request->quantities as $key => $quantity) {
                if (!empty($request->items[$key])) {
                    DB::table('laboratory_accountability_items')->insert([
                        'accountability_id' => $accountabilityId,
                        'quantity' => $quantity,
                        'item' => $request->items[$key],
                        'description' => $request->descriptions[$key] ?? null,
                        'issued_condition' => $request->issued_conditions[$key] ?? null,
                        'returned_condition' => $request->returned_conditions[$key] ?? null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Laboratory Equipment Accountability form has been submitted successfully. Control Number: ' . $controlNo);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while saving the form. Please try again. Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $accountability = DB::table('laboratory_accountability')
            ->where('accountability_id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$accountability) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $items = DB::table('laboratory_accountability_items')
            ->where('accountability_id', $id)
            ->get();

        return view('laboratory.accountability.show', compact('accountability', 'items'));
    }

    public function approve($id)
    {
        try {
            DB::table('laboratory_accountability')
                ->where('accountability_id', $id)
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
            DB::table('laboratory_accountability')
                ->where('accountability_id', $id)
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
            DB::table('laboratory_accountability')
                ->where('accountability_id', $id)
                ->update([
                    'deleted_at' => now()
                ]);

            return response()->json(['message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting record'], 500);
        }
    }
} 