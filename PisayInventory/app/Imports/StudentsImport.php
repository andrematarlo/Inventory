<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\UserAccount;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
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
use Carbon\Carbon;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    protected $columnMapping;
    protected $createdById;
    protected $rowCount = 0;
    protected $skippedRows = [];
    protected $successCount = 0;
    protected $duplicateRows = [];

    public function __construct(array $columnMapping, $createdById)
    {
        $this->columnMapping = $columnMapping;
        $this->createdById = $createdById;
    }

    public function collection(Collection $rows)
    {
        Log::info('Starting import process with rows:', ['row_count' => count($rows)]);

        foreach ($rows as $index => $row) {
            try {
                // Convert row keys to snake_case for consistent mapping
                $rowData = collect($row)->mapWithKeys(function ($value, $key) {
                    return [str_replace(' ', '_', strtolower($key)) => $value];
                })->toArray();

                Log::info('Processing row:', ['row_data' => $rowData]);

                // Map the data according to column mapping
                $studentData = [
                    'student_id' => $rowData['student_number'] ?? null,
                    'first_name' => $rowData['first_name'] ?? null,
                    'last_name' => $rowData['last_name'] ?? null,
                    'middle_name' => $rowData['middle_name'] ?? null,
                    'email' => $rowData['email'] ?? null,
                    'contact_number' => (string)($rowData['contact_number'] ?? ''),
                    'grade_level' => (string)($rowData['grade_level'] ?? ''),
                    'section' => $rowData['section'] ?? null,
                    'address' => $rowData['address'] ?? null,
                ];

                // Handle date_of_birth with multiple formats
                if (isset($rowData['birthdate']) && !empty($rowData['birthdate'])) {
                    try {
                        $value = $rowData['birthdate'];
                        $date = null;

                        // Check if the value is a number (Excel date)
                        if (is_numeric($value)) {
                            // Convert Excel date number to PHP DateTime
                            $date = Carbon::createFromDate(1899, 12, 30)->addDays($value);
                            Log::info('Parsed Excel numeric date:', [
                                'original' => $value,
                                'parsed' => $date->format('Y-m-d')
                            ]);
                        } else {
                            // Try different string date formats
                            $dateFormats = [
                                'm/d/Y',    // 11/01/2001
                                'd/m/Y',    // 01/11/2001
                                'Y-m-d',    // 2001-11-01
                                'd-m-Y',    // 01-11-2001
                                'Y/m/d',    // 2001/11/01
                                'm-d-Y',    // 11-01-2001
                                'M d Y',    // Nov 01 2001
                                'd M Y',    // 01 Nov 2001
                            ];

                            foreach ($dateFormats as $format) {
                                try {
                                    $date = Carbon::createFromFormat($format, $value);
                                    if ($date !== false) {
                                        Log::info('Parsed string date:', [
                                            'original' => $value,
                                            'format' => $format,
                                            'parsed' => $date->format('Y-m-d')
                                        ]);
                                        break;
                                    }
                                } catch (\Exception $e) {
                                    continue;
                                }
                            }
                        }

                        if ($date) {
                            $studentData['date_of_birth'] = $date->format('Y-m-d');
                            Log::info('Final date value:', ['date_of_birth' => $studentData['date_of_birth']]);
                        } else {
                            Log::warning('Could not parse date:', ['value' => $value]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to parse date:', [
                            'value' => $rowData['birthdate'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Handle gender with flexible formats
                if (isset($rowData['gender'])) {
                    $gender = strtoupper(trim($rowData['gender']));
                    if ($gender === 'M' || str_starts_with($gender, 'MALE')) {
                        $studentData['gender'] = 'Male';
                    } elseif ($gender === 'F' || str_starts_with($gender, 'FEMALE')) {
                        $studentData['gender'] = 'Female';
                    } else {
                        Log::warning('Unknown gender format:', ['value' => $rowData['gender']]);
                    }
                }

                // Add metadata
                $studentData['created_by'] = $this->createdById;
                $studentData['status'] = 'Active';

                Log::info('Mapped student data:', ['data' => $studentData]);

                // Validate required fields
                if (empty($studentData['student_id']) || 
                    empty($studentData['first_name']) || 
                    empty($studentData['last_name'])) {
                    throw new \Exception('Missing required fields');
                }

                // Check for existing student
                $existingStudent = Student::where('student_id', $studentData['student_id'])->first();

                // Add check for existing username in UserAccount
                $username = strtolower($studentData['first_name'] . '.' . $studentData['last_name']);
                $existingUserAccount = UserAccount::where('Username', $username)->first();

                if ($existingStudent || $existingUserAccount) {
                    $this->duplicateRows[] = [
                        'row' => $index + 2,
                        'student_id' => $studentData['student_id'],
                        'name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
                        'reason' => $existingStudent ? 'Student ID already exists' : 'Username already exists'
                    ];
                    continue; // Skip this record but continue with next one
                }

                // Create user account
                $userAccount = UserAccount::create([
                    'Username' => strtolower($studentData['first_name'] . '.' . $studentData['last_name']),
                    'Password' => Hash::make('password123'),
                    'CreatedByID' => $this->createdById,
                    'DateCreated' => now(),
                    'IsDeleted' => 0
                ]);

                // Create student with UserAccountID
                $studentData['UserAccountID'] = $userAccount->UserAccountID;
                Student::create($studentData);

                $this->successCount++;
                Log::info('Created new student with user account:', [
                    'student_id' => $studentData['student_id'],
                    'username' => $userAccount->Username
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing row:', [
                    'row_number' => $index + 2,
                    'error' => $e->getMessage()
                ]);
                
                $this->skippedRows[] = [
                    'row' => $index + 2,
                    'reason' => $e->getMessage()
                ];
                
                // Continue processing other records
                continue;
            }
            
            $this->rowCount++;
        }

        Log::info('Import completed:', [
            'total_rows' => $this->rowCount,
            'successful_imports' => $this->successCount,
            'skipped_rows' => count($this->skippedRows),
            'duplicate_rows' => count($this->duplicateRows)
        ]);
    }

    public function rules(): array
    {
        return [
            'student_number' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'nullable|email',
            'contact_number' => 'nullable',
            'gender' => 'nullable',
            'birthdate' => 'nullable',
            'address' => 'nullable',
            'grade_level' => 'nullable',
            'section' => 'nullable'
        ];
    }

    public function onError(\Throwable $e)
    {
        Log::error('Import error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Row validation failure:', [
                'row' => $failure->row(),
                'errors' => $failure->errors()
            ]);
            
            $this->skippedRows[] = [
                'row' => $failure->row(),
                'reason' => implode(', ', $failure->errors())
            ];
        }
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkippedRows()
    {
        return $this->skippedRows;
    }

    public function getDuplicateRows()
    {
        return $this->duplicateRows;
    }
}