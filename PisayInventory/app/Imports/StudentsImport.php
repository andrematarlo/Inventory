<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Http\Request;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    protected $request;
    protected $rowCount = 0;
    protected $columnMapping;
    protected $defaultValues;
    protected $createdById;
    protected $skippedRows = [];
    protected $successCount = 0;

    /**
     * Create a new import instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request, $createdById)
    {
        $this->request = $request;
        $this->columnMapping = $request->input('column_mapping', []);
        $this->defaultValues = [
            'middle_name' => $request->input('default_middle_name'),
            'gender' => $request->input('default_gender'),
            'email' => $request->input('default_email'),
            'grade_level' => $request->input('default_grade_level'),
            'section' => $request->input('default_section'),
        ];
        $this->createdById = $createdById;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Skip empty rows
                if (empty($this->getValue($row, 'student_id')) || 
                    empty($this->getValue($row, 'first_name')) || 
                    empty($this->getValue($row, 'last_name'))) {
                    continue;
                }

                // Check if student already exists
                $existingStudent = Student::where('student_id', $this->getValue($row, 'student_id'))->first();
                
                if ($existingStudent) {
                    // Update existing student
                    $existingStudent->first_name = $this->getValue($row, 'first_name');
                    $existingStudent->last_name = $this->getValue($row, 'last_name');
                    $existingStudent->middle_name = $this->getValue($row, 'middle_name');
                    $existingStudent->gender = $this->getValue($row, 'gender');
                    $existingStudent->email = $this->getValue($row, 'email');
                    $existingStudent->grade_level = $this->getValue($row, 'grade_level');
                    $existingStudent->section = $this->getValue($row, 'section');
                    $existingStudent->save();
                } else {
                    // Create new student
                    Student::create([
                        'student_id' => $this->getValue($row, 'student_id'),
                        'first_name' => $this->getValue($row, 'first_name'),
                        'last_name' => $this->getValue($row, 'last_name'),
                        'middle_name' => $this->getValue($row, 'middle_name'),
                        'gender' => $this->getValue($row, 'gender'),
                        'email' => $this->getValue($row, 'email'),
                        'grade_level' => $this->getValue($row, 'grade_level'),
                        'section' => $this->getValue($row, 'section'),
                        'status' => 'Active',
                        'created_at' => now(),
                        'created_by' => $this->createdById
                    ]);
                }

                $this->rowCount++;
                $this->successCount++;

            } catch (\Exception $e) {
                Log::error('Error importing student row:', [
                    'error' => $e->getMessage(),
                    'row' => $row,
                    'creator_id' => $this->createdById
                ]);
                
                $this->skippedRows[] = [
                    'student_id' => $this->getValue($row, 'student_id', 'Unknown'),
                    'reason' => 'Error: ' . $e->getMessage()
                ];
            }
        }
    }

    /**
     * Get a value from the row based on the column mapping or default value.
     *
     * @param  mixed  $row
     * @param  string  $field
     * @return mixed
     */
    protected function getValue($row, $field, $default = '')
    {
        // If there's a column mapping for this field and the value exists in the row
        if (isset($this->columnMapping[$field]) && !empty($this->columnMapping[$field])) {
            $columnName = $this->columnMapping[$field];
            if (isset($row[$columnName]) && !empty($row[$columnName])) {
                return $row[$columnName];
            }
        }

        // Otherwise, use the default value if available
        if (isset($this->defaultValues[$field]) && !empty($this->defaultValues[$field])) {
            return $this->defaultValues[$field];
        }

        return $default;
    }

    public function rules(): array
    {
        // The validation should use the mapped column names from the Excel file
        $mappedColumns = array_values($this->columnMapping);
        
        $rules = [];
        foreach ($mappedColumns as $excelColumn) {
            if ($excelColumn === $this->columnMapping['student_id']) {
                $rules[$excelColumn] = 'required|string|max:50';
            } elseif ($excelColumn === $this->columnMapping['first_name']) {
                $rules[$excelColumn] = 'required|string|max:255';
            } elseif ($excelColumn === $this->columnMapping['last_name']) {
                $rules[$excelColumn] = 'required|string|max:255';
            }
        }
        
        return $rules;
    }

    public function customValidationMessages()
    {
        // Use the mapped column name in the error message
        $studentIdColumn = $this->columnMapping['student_id'] ?? '';
        $firstNameColumn = $this->columnMapping['first_name'] ?? '';
        $lastNameColumn = $this->columnMapping['last_name'] ?? '';
        
        return [
            $studentIdColumn.'.required' => 'The student ID is required.',
            $firstNameColumn.'.required' => 'The first name is required.',
            $lastNameColumn.'.required' => 'The last name is required.',
        ];
    }

    public function onError(\Throwable $e)
    {
        Log::error('Excel import error: ' . $e->getMessage());
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->skippedRows[] = [
                'row' => $failure->row(),
                'reason' => implode(', ', $failure->errors())
            ];
        }
    }

    public function getSkippedRows()
    {
        return $this->skippedRows;
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
} 