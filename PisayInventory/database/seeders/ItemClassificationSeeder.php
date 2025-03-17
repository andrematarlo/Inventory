<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemClassificationSeeder extends Seeder
{
    public function run()
    {
        $classifications = [
            ['ClassificationName' => 'Food', 'IsDeleted' => false],
            ['ClassificationName' => 'Beverages', 'IsDeleted' => false],
            ['ClassificationName' => 'Snacks', 'IsDeleted' => false],
            ['ClassificationName' => 'School Supplies', 'IsDeleted' => false],
        ];

        foreach ($classifications as $classification) {
            DB::table('itemclassification')->insert($classification);
        }
    }
} 