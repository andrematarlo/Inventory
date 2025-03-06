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

class StudentsController extends Controller
{
    /**
     * Display a listing of the students.
     *
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check if user has permission to add students
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

        // Create the student
        Student::create($request->all());

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return response()->json([
                'success' => false,
                'sweet_alert' => [
                    'type' => 'error',
                    'title' => 'Access Denied',
                    'message' => 'You do not have permission to import students.'
                ]
            ]);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('temp');
            
            $spreadsheet = IOFactory::load(storage_path('app/' . $path));
            $worksheet = $spreadsheet->getActiveSheet();
            $headers = [];
            $previewData = [];
            
            // Get headers
            foreach ($worksheet->getRowIterator(1, 1) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    if (!empty($cell->getValue())) {
                        $headers[] = $cell->getValue();
                    }
                }
            }
            
            // Get preview data (first 5 rows)
            $rowCount = min($worksheet->getHighestRow(), 6);
            for ($rowIndex = 2; $rowIndex <= $rowCount; $rowIndex++) {
                $rowData = [];
                foreach ($worksheet->getRowIterator($rowIndex, $rowIndex) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }
                }
                if (array_filter($rowData)) {
                    $previewData[] = $rowData;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'headers' => $headers,
                    'preview_data' => $previewData,
                    'file_path' => $path
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Excel preview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'sweet_alert' => [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'Error reading Excel file. Please make sure it\'s a valid Excel file.'
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
    public function import(Request $request)
    {
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return response()->json([
                'success' => false,
                'sweet_alert' => [
                    'type' => 'error',
                    'title' => 'Access Denied',
                    'message' => 'You do not have permission to import students.'
                ]
            ]);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
            'column_mapping' => 'required|array',
            'column_mapping.student_id' => 'required|string',
            'column_mapping.first_name' => 'required|string',
            'column_mapping.last_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'sweet_alert' => [
                    'type' => 'error',
                    'title' => 'Validation Error',
                    'message' => $validator->errors()->first()
                ],
                'errors' => $validator->errors()
            ]);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->path());
            $worksheet = $spreadsheet->getActiveSheet();
            $columnMapping = $request->column_mapping;
            $successCount = 0;
            $errorRows = [];
            $duplicates = [];

            // Process each row
            $highestRow = $worksheet->getHighestRow();
            for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
                try {
                    $rowData = [];
                    foreach ($columnMapping as $field => $columnLetter) {
                        if (!empty($columnLetter)) {
                            $cellValue = $worksheet->getCell($columnLetter . $rowIndex)->getValue();
                            $rowData[$field] = $cellValue;
                        }
                    }

                    // Skip empty rows
                    if (empty(array_filter($rowData))) {
                        continue;
                    }

                    // Validate required fields
                    if (empty($rowData['student_id']) || empty($rowData['first_name']) || empty($rowData['last_name'])) {
                        $errorRows[] = "Row {$rowIndex}: Missing required fields (Student ID, First Name, or Last Name)";
                        continue;
                    }

                    // Check for duplicate student ID
                    if (Student::where('student_id', $rowData['student_id'])->exists()) {
                        $duplicates[] = $rowData['student_id'];
                        continue;
                    }

                    // Create student
                    Student::create([
                        'student_id' => $rowData['student_id'],
                        'first_name' => $rowData['first_name'],
                        'last_name' => $rowData['last_name'],
                        'middle_name' => $rowData['middle_name'] ?? null,
                        'email' => $rowData['email'] ?? null,
                        'contact_number' => $rowData['contact_number'] ?? null,
                        'gender' => $rowData['gender'] ?? null,
                        'grade_level' => $rowData['grade_level'] ?? null,
                        'section' => $rowData['section'] ?? null,
                        'status' => 'Active',
                        'created_by' => Auth::id()
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorRows[] = "Row {$rowIndex}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "{$successCount} students imported successfully.";
            $alertType = 'success';
            
            if (!empty($errorRows) || !empty($duplicates)) {
                $alertType = 'warning';
                if (!empty($duplicates)) {
                    $message .= " " . count($duplicates) . " duplicate student IDs were skipped.";
                }
                if (!empty($errorRows)) {
                    $message .= " There were " . count($errorRows) . " errors during import.";
                }
            }

            return response()->json([
                'success' => true,
                'sweet_alert' => [
                    'type' => $alertType,
                    'title' => 'Import Complete',
                    'message' => $message
                ],
                'details' => [
                    'success_count' => $successCount,
                    'error_rows' => $errorRows,
                    'duplicates' => $duplicates
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'sweet_alert' => [
                    'type' => 'error',
                    'title' => 'Import Failed',
                    'message' => 'Error importing students: ' . $e->getMessage()
                ]
            ]);
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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