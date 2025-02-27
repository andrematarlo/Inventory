<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Role;

class AssignAdminRole extends Command
{
    protected $signature = 'role:assign-admin';
    protected $description = 'Assign admin role to the first user';

    public function handle()
    {
        $user = Employee::first();
        $adminRole = Role::where('name', 'Admin')->first();

        if (!$user || !$adminRole) {
            $this->error('User or Admin role not found');
            return 1;
        }

        $user->roles()->sync([$adminRole->id]);
        $this->info('Admin role assigned successfully');
        return 0;
    }
}
