<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuppliersTableSeeder extends Seeder
{
    public function run()
    {
        // Add some sample suppliers
        Supplier::create([
            'SupplierName' => 'Sample Supplier 1',
            'Address' => 'Sample Address 1',
            'ContactNumber' => '1234567890',
            'Email' => 'supplier1@example.com',
            'CreatedById' => 1,
            'DateCreated' => Carbon::now(),
            'IsDeleted' => false
        ]);

        // Add more suppliers as needed
    }
} 