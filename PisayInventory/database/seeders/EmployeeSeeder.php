<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        Employee::create([
            'FirstName' => 'Admin',
            'LastName' => 'User',
            'Email' => 'admin@example.com',
            'Position' => 'System Administrator',
            'Department' => 'IT'
        ]);
    }
} 