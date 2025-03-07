<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;


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
     * Show the form for importing students.
     *
     */
    public function showImport()
    {
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
            if (!$request->hasFile('file')) {
                return back()->with('error', 'Please upload a file');
            }

            $file = $request->file('file');
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $headers = [];
            
            // Get headers from first row
            foreach ($worksheet->getRowIterator(1, 1) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue()) {
                        $headers[] = $cell->getValue();
                    }
                }
            }

            // Get first few rows of data for preview
            $previewData = [];
            foreach ($worksheet->getRowIterator(2, 6) as $row) {
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                if (array_filter($rowData)) {
                    $previewData[] = $rowData;
                }
            }

            return view('students.preview-columns', compact('headers', 'previewData'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error reading file: ' . $e->getMessage());
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
            $validated = $request->validate([
                'file' => 'required|file|mimes:xlsx,xls',
                'column_mapping' => 'required|array'
            ]);

            $file = $request->file('file');
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            
            DB::beginTransaction();

            foreach ($worksheet->getRowIterator(2) as $row) {
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                if (array_filter($rowData)) {
                    $studentData = [];
                    foreach ($request->column_mapping as $field => $columnIndex) {
                        if ($columnIndex !== '') {
                            $studentData[$field] = $rowData[$columnIndex] ?? null;
                        }
                    }

                    Student::create($studentData);
                }
            }

            DB::commit();
            return redirect()->route('students.index')->with('success', 'Students imported successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error importing students: ' . $e->getMessage());
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