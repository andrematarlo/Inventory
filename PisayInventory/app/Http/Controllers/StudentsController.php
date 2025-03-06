<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;


class StudentsController extends Controller
{
    /**
<<<<<<< HEAD
=======
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
>>>>>>> da497faeed4a7f3999f503b3e6f75edd32cd33b6
     * Show the form for importing students.
     *
     */
    public function showImport()
    {
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to import students.');
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
<<<<<<< HEAD
    {
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to import students.');
        }

=======
{
    try {
>>>>>>> da497faeed4a7f3999f503b3e6f75edd32cd33b6
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
<<<<<<< HEAD
            
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

            return redirect()->back()
                ->with('headers', $headers)
                ->with('preview_data', $previewData)
                ->with('file_path', $path);

        } catch (\Exception $e) {
            Log::error('Excel preview error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error reading Excel file. Please make sure it\'s a valid Excel file.');
=======
>>>>>>> da497faeed4a7f3999f503b3e6f75edd32cd33b6
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
<<<<<<< HEAD
    public function import(Request $request)
    {
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to import students.');
        }

        $request->validate([
            'file_path' => 'required|string',
            'column_mapping' => 'required|array',
            'column_mapping.*' => 'nullable|string',
        ]);

=======
    public function processImport(Request $request)
{
    try {
        // Validate request
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
            'column_mapping' => 'required|array'
        ]);

        DB::beginTransaction();

>>>>>>> da497faeed4a7f3999f503b3e6f75edd32cd33b6
        try {
            $import = new StudentsImport(
                $request->column_mapping,
                Auth::id()
            );

<<<<<<< HEAD
            $filePath = storage_path('app/' . $request->file_path);
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $columnMapping = $request->column_mapping;
            $successCount = 0;
            $errorRows = [];

            // Process each row
            $highestRow = $worksheet->getHighestRow();
            for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
                try {
                    $rowData = [];
                    foreach ($columnMapping as $columnIndex => $field) {
                        if (!empty($field)) {
                            $cellValue = $worksheet->getCellByColumnAndRow($columnIndex + 1, $rowIndex)->getValue();
                            $rowData[$field] = $cellValue;
                        }
                    }

                    // Skip empty rows
                    if (empty(array_filter($rowData))) {
                        continue;
                    }

                    // Validate required fields
                    if (empty($rowData['StudentNumber']) || empty($rowData['FirstName']) || empty($rowData['LastName'])) {
                        $errorRows[] = "Row {$rowIndex}: Missing required fields (Student Number, First Name, or Last Name)";
                        continue;
                    }

                    // Check for duplicate student number
                    if (Student::where('StudentNumber', $rowData['StudentNumber'])->exists()) {
                        $errorRows[] = "Row {$rowIndex}: Duplicate Student Number ({$rowData['StudentNumber']})";
                        continue;
                    }

                    // Create student
                    $student = new Student($rowData);
                    $student->Status = 'Active';
                    $student->save();
                    $successCount++;

                } catch (\Exception $e) {
                    $errorRows[] = "Row {$rowIndex}: " . $e->getMessage();
                }
            }

            DB::commit();

            // Clean up temporary file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $message = "{$successCount} students imported successfully.";
            if (!empty($errorRows)) {
                $message .= " However, there were some errors:";
                return redirect()->route('students.index')
                    ->with('success', $message)
                    ->with('import_errors', $errorRows);
            }

            return redirect()->route('students.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student import error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error importing students. Please try again.');
=======
            Excel::import($import, $request->file('excel_file'));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Students imported successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
>>>>>>> da497faeed4a7f3999f503b3e6f75edd32cd33b6
        }

    } catch (\Exception $e) {
        Log::error('Error importing students:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Error importing students: ' . $e->getMessage()
        ], 500);
    }
<<<<<<< HEAD
=======
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
>>>>>>> da497faeed4a7f3999f503b3e6f75edd32cd33b6
} 