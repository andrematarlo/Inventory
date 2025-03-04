<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fields;
    protected $employeesStatus;

    public function __construct(array $fields, string $employeesStatus)
    {
        $this->fields = $fields;
        $this->employeesStatus = $employeesStatus;
    }

    public function collection()
    {
        $query = Employee::with(['roles', 'createdBy']);

        switch ($this->employeesStatus) {
            case 'active':
                $query->where('IsDeleted', false);
                break;
            case 'deleted':
                $query->where('IsDeleted', true);
                break;
            // 'all' doesn't need additional conditions
        }

        return $query->get();
    }

    public function headings(): array
    {
        return array_map(function($field) {
            return $this->getReadableColumnName($field);
        }, $this->fields);
    }

    public function map($employee): array
    {
        $row = [];
        foreach ($this->fields as $field) {
            switch ($field) {
                case 'FirstName':
                    $row[] = $employee->FirstName;
                    break;
                case 'LastName':
                    $row[] = $employee->LastName;
                    break;
                case 'Email':
                    $row[] = $employee->Email;
                    break;
                case 'Gender':
                    $row[] = $employee->Gender;
                    break;
                case 'Role':
                    $row[] = $employee->Role;
                    break;
                case 'Address':
                    $row[] = $employee->Address;
                    break;
            }
        }
        return $row;
    }

    private function getReadableColumnName($field): string
    {
        $names = [
            'FirstName' => 'First Name',
            'LastName' => 'Last Name',
            'Email' => 'Email',
            'Gender' => 'Gender',
            'Role' => 'Role',
            'Address' => 'Address'
        ];

        return $names[$field] ?? $field;
    }
}