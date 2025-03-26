<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassificationSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        
        $classifications = [
            [
                'ClassificationName' => 'Food',
                'IsDeleted' => false,
                'DateCreated' => $now
            ],
            [
                'ClassificationName' => 'Beverages',
                'IsDeleted' => false,
                'DateCreated' => $now
            ],
            [
                'ClassificationName' => 'Snacks',
                'IsDeleted' => false,
                'DateCreated' => $now
            ],
            [
                'ClassificationName' => 'School Supplies',
                'IsDeleted' => false,
                'DateCreated' => $now
            ],
        ];

        foreach ($classifications as $classification) {
            DB::table('classification')->insert($classification);
        }
    }
} 