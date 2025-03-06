<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\StudentRoleSeeder;

class SetupStudentAndLabModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:student-lab-modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations and seeders for Student and Laboratory Reservation modules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Student and Laboratory Reservation modules...');

        $this->info('Running migrations...');
        $this->runMigrations();

        $this->info('Creating Student role...');
        $this->runSeeders();

        $this->info('Adding modules to database...');
        $this->addModules();

        $this->info('Setup completed successfully!');
    }

    private function runMigrations()
    {
        try {
            $this->info('Creating students table...');
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2024_07_20_000001_create_students_table.php'
            ]);
            $this->info('Students table created successfully.');

            $this->info('Creating laboratory_reservations table...');
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2024_07_20_000002_create_laboratory_reservations_table.php'
            ]);
            $this->info('Laboratory reservations table created successfully.');
        } catch (\Exception $e) {
            $this->error('Error running migrations: ' . $e->getMessage());
        }
    }

    private function runSeeders()
    {
        try {
            $seeder = new StudentRoleSeeder();
            $seeder->run();
            $this->info('Student role created successfully.');
        } catch (\Exception $e) {
            $this->error('Error running seeders: ' . $e->getMessage());
        }
    }

    private function addModules()
    {
        try {
            // Check if modules already exist
            $studentModule = DB::table('modules')->where('ModuleName', 'Students')->first();
            $labModule = DB::table('modules')->where('ModuleName', 'Laboratory Reservations')->first();

            if (!$studentModule) {
                DB::table('modules')->insert([
                    'ModuleName' => 'Students',
                    'CreatedAt' => now(),
                    'UpdatedAt' => now()
                ]);
                $this->info('Students module added successfully.');
            } else {
                $this->info('Students module already exists.');
            }

            if (!$labModule) {
                DB::table('modules')->insert([
                    'ModuleName' => 'Laboratory Reservations',
                    'CreatedAt' => now(),
                    'UpdatedAt' => now()
                ]);
                $this->info('Laboratory Reservations module added successfully.');
            } else {
                $this->info('Laboratory Reservations module already exists.');
            }
        } catch (\Exception $e) {
            $this->error('Error adding modules: ' . $e->getMessage());
        }
    }
} 