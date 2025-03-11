<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use App\Models\UserAccount;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class StudentsController extends Controller
{
    /**
     * Display a listing of the students.
     *

     */
    public function index()
    {
        // Check if user has permission to view students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view students.');
        }

        $students = Student::orderBy('last_name')->get();
        $deletedCount = Student::onlyTrashed()->count();
        
        return view('students.index', compact('students', 'userPermissions', 'deletedCount'));
    }

    /**
     * Display a listing of deleted students.
     *
     */
    public function trash()
    {
        // Check if user has permission to view students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view deleted students.');
        }

        $deletedStudents = Student::onlyTrashed()->orderBy('last_name')->get();
        return view('students.trash', compact('deletedStudents', 'userPermissions'));
    }

    /**
     * Show the form for creating a new student.
     *
     */
    public function create()
    {
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to add students.');
        }

        return view('students.create', compact('userPermissions'));
    }

    /**
     * Store a newly created student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
{
    // Check permissions
    $userPermissions = $this->getUserPermissions('Students');
    if (!$userPermissions || !$userPermissions->CanAdd) {
        return redirect()->route('students.index')->with('error', 'You do not have permission to add students.');
    }

    // Validate the request
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|string|max:20|unique:students,student_id',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'email' => 'nullable|email|max:100|unique:students,email',
        'contact_number' => 'nullable|string|max:20',
        'gender' => 'required|in:Male,Female,Other',
        'date_of_birth' => 'nullable|date',
        'address' => 'nullable|string',
        'grade_level' => 'nullable|string|max:20',
        'section' => 'nullable|string|max:20',
        'status' => 'nullable|in:Active,Inactive'
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        // Create user account
        $userAccount = UserAccount::create([  // Use UserAccount model, not User
            'Username' => strtolower($request->first_name . '.' . $request->last_name),
            'Password' => Hash::make('password123'),
            'CreatedByID' => Auth::id(),
            'DateCreated' => now(),
            'IsDeleted' => 0
        ]);

        // The role will be automatically set to 'Students' by the boot method

        // Create student
        $student = Student::create([
            'student_id' => $request->student_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'grade_level' => $request->grade_level,
            'section' => $request->section,
            'status' => 'Active',
            'UserAccountID' => $userAccount->UserAccountID
        ]);

        DB::commit();
        return redirect()->route('students.index')
            ->with('success', 'Student created successfully with login credentials.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Error creating student: ' . $e->getMessage())
            ->withInput();
    }
}
    /**
     * Display the specified student.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        // Check if user has permission to view students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanView) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view students.');
        }

        $student = Student::findOrFail($id);
        
        return view('students.show', compact('student', 'userPermissions'));
    }

    /**
     * Show the form for editing the specified student.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        // Check if user has permission to edit students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to edit students.');
        }

        $student = Student::findOrFail($id);
        
        return view('students.edit', compact('student', 'userPermissions'));
    }

    /**
     * Update the specified student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id)
    {
        // Check if user has permission to edit students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanEdit) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to edit students.');
        }

        $student = Student::findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string|max:20|unique:students,student_id,' . $id,
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'nullable|email|max:100|unique:students,email,' . $id,
            'contact_number' => 'nullable|string|max:20',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'grade_level' => 'nullable|string|max:20',
            'section' => 'nullable|string|max:20',
            'status' => 'required|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update the student
        $student->update($request->all());

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {
        // Check if user has permission to delete students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanDelete) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to delete students.');
        }

        $student = Student::findOrFail($id);
        
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    /**
     * Show the form for importing students.
     *
     */
    public function showImport()
    {
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('students.index')->with('sweet_alert', [
                'type' => 'error',
                'title' => 'Access Denied',
                'message' => 'You do not have permission to import students.'
            ]);
        }

        return view('students.import');
    }

    /**
     * Preview Excel columns for mapping.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function previewColumns(Request $request)
{
    try {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        // Use getRealPath() instead of storing the file
        $path = $request->file('excel_file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $columns = [];

        // Get the first row (headers)
        foreach ($worksheet->getRowIterator(1, 1) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $colName = trim($cell->getValue());
                if (!empty($colName)) {
                    // Clean the column name
                    $colName = str_replace(['_', '-'], ' ', $colName);
                    $colName = trim($colName);
                    $columns[] = $colName;
                }
            }
        }

        Log::info('Excel columns found:', ['columns' => $columns]);

        return response()->json([
            'success' => true,
            'data' => [
                'headers' => $columns
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error previewing Excel columns: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'sweet_alert' => [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error reading Excel file: ' . $e->getMessage()
            ]
        ]);
    }
}
    /**
     * Import students from Excel file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function processImport(Request $request)
{
    try {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
            'column_mapping' => 'required|array'
        ]);

        DB::beginTransaction();

        try {
            $import = new StudentsImport(
                $request->column_mapping,
                Auth::id()
            );

            Excel::import($import, $request->file('excel_file'));

            DB::commit();

            $successCount = $import->getSuccessCount();
            $duplicateCount = count($import->getDuplicateRows());

            if ($successCount === 0 && $duplicateCount > 0) {
                return response()->json([
                    'success' => false,
                    'title' => 'Import Failed',
                    'message' => "No new records were imported. All {$duplicateCount} records already exist in the system.",
                    'showDetailsButton' => true,
                    'details' => [
                        'imported' => $successCount,
                        'duplicates' => $import->getDuplicateRows()
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'title' => 'Import Successful',
                'message' => "Import completed. {$successCount} records imported successfully. {$duplicateCount} duplicate records were skipped.",
                'showDetailsButton' => $duplicateCount > 0,
                'details' => [
                    'imported' => $successCount,
                    'duplicates' => $import->getDuplicateRows()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (\Exception $e) {
        Log::error('Import error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'title' => 'Import Failed',
            'message' => 'Error importing students: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Get the permissions for a specific module for the authenticated user.
     *
     * @param string $moduleName
     * @return \App\Models\RolePolicy|null
     */
    public function getUserPermissions($moduleName)
    {
        return parent::getUserPermissions($moduleName);
    }

    /**
     * Restore a soft-deleted student.
     *
     * @param  int  $id
     */
    public function restore($id)
    {
        // Check if user has permission to restore students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanDelete) {
            return redirect()->route('students.index')
                ->with('error', 'You do not have permission to restore students.');
        }

        try {
            $student = Student::withTrashed()->findOrFail($id);
            $student->restore();

            return redirect()->route('students.index')
                ->with('success', 'Student restored successfully.');
        } catch (\Exception $e) {
            return redirect()->route('students.index')
                ->with('error', 'Error restoring student: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete the specified student from storage.
     *
     * @param  int  $id
     */
    public function forceDelete($id)
    {
        // Check if user has permission to delete students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanDelete) {
            return redirect()->route('students.trash')->with('error', 'You do not have permission to permanently delete students.');
        }

        $student = Student::withTrashed()->findOrFail($id);
        $student->forceDelete();

        return redirect()->route('students.trash')->with('success', 'Student permanently deleted successfully.');
    }
} 