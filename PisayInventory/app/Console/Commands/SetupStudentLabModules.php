<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\RolePolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupStudentLabModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:student-modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up Student modules and related tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Setting up Student modules...');

        // Create database tables
        $this->createStudentsTable();

        // Create roles and policies
        $this->setupRolesAndPolicies();

        $this->info('Student modules have been set up successfully!');

        return 0;
    }

    /**
     * Create the students table.
     *
     * @return void
     */
    protected function createStudentsTable()
    {
        if (!Schema::hasTable('students')) {
            $this->info('Creating students table...');

            Schema::create('students', function ($table) {
                $table->id('StudentID');
                $table->string('StudentNumber', 20)->unique();
                $table->string('FirstName', 50);
                $table->string('LastName', 50);
                $table->string('MiddleName', 50)->nullable();
                $table->enum('Gender', ['Male', 'Female']);
                $table->string('ContactNumber', 20)->nullable();
                $table->string('Email', 100)->nullable();
                $table->string('YearLevel', 20)->nullable();
                $table->string('Section', 20)->nullable();
                $table->enum('Status', ['Active', 'Inactive', 'Graduated', 'Transferred'])->default('Active');
                $table->timestamps();
            });

            $this->info('Students table created successfully.');
        } else {
            $this->info('Students table already exists.');
        }
    }

    /**
     * Set up roles and policies for the new modules.
     *
     * @return void
     */
    protected function setupRolesAndPolicies()
    {
        $this->info('Setting up roles and policies...');

        // Create Student Coordinator role if it doesn't exist
        $studentCoordinatorRole = Role::where('RoleName', 'Student Coordinator')->first();
        if (!$studentCoordinatorRole) {
            $studentCoordinatorRole = new Role();
            $studentCoordinatorRole->RoleName = 'Student Coordinator';
            $studentCoordinatorRole->Description = 'Manages students';
            $studentCoordinatorRole->save();
            $this->info('Student Coordinator role created.');
        } else {
            $this->info('Student Coordinator role already exists.');
        }

        // Get admin role
        $adminRole = Role::where('RoleName', 'Admin')->first();
        if (!$adminRole) {
            $this->error('Admin role not found!');
            return;
        }

        // Create policies for the Student module
        $this->createOrUpdatePolicy($studentCoordinatorRole->RoleId, 'Students', true, true, true, true);
        $this->createOrUpdatePolicy($adminRole->RoleId, 'Students', true, true, true, true);

        $this->info('Roles and policies set up successfully.');
    }

    /**
     * Create or update a role policy.
     */
    protected function createOrUpdatePolicy($roleId, $moduleName, $canView, $canAdd, $canEdit, $canDelete)
    {
        // First ensure the module exists
        DB::table('modules')->insertOrIgnore([
            'ModuleName' => $moduleName,
            'CreatedAt' => now(),
            'UpdatedAt' => now()
        ]);

        $policy = RolePolicy::where('RoleId', $roleId)
                           ->where('Module', $moduleName)
                           ->first();

        if (!$policy) {
            $policy = new RolePolicy();
            $policy->RoleId = $roleId;
            $policy->Module = $moduleName;
        }

        $policy->CanView = $canView;
        $policy->CanAdd = $canAdd;
        $policy->CanEdit = $canEdit;
        $policy->CanDelete = $canDelete;
        $policy->DateCreated = now();
        $policy->CreatedById = 1; // System user
        $policy->save();

        $this->info("Policy for {$moduleName} module updated for role ID {$roleId}.");
    }
} 