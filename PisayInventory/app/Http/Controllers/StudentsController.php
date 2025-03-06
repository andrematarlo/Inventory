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
     * Show the form for importing students.
     *
     * @return \Illuminate\Http\Response
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
    {
        // Check if user has permission to add students
        $userPermissions = $this->getUserPermissions('Students');
        if (!$userPermissions || !$userPermissions->CanAdd) {
            return redirect()->route('students.index')->with('error', 'You do not have permission to import students.');
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

            return redirect()->back()
                ->with('headers', $headers)
                ->with('preview_data', $previewData)
                ->with('file_path', $path);

        } catch (\Exception $e) {
            Log::error('Excel preview error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error reading Excel file. Please make sure it\'s a valid Excel file.');
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
            return redirect()->route('students.index')->with('error', 'You do not have permission to import students.');
        }

        $request->validate([
            'file_path' => 'required|string',
            'column_mapping' => 'required|array',
            'column_mapping.*' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

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
        }
    }
} 