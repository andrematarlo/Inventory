<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'ItemName' => 'Hamburger',
                'UnitPrice' => 50.00,
                'ClassificationID' => 1, // Food
                'IsDeleted' => false
            ],
            [
                'ItemName' => 'Coca Cola',
                'UnitPrice' => 25.00,
                'ClassificationID' => 2, // Beverages
                'IsDeleted' => false
            ],
            [
                'ItemName' => 'Chips',
                'UnitPrice' => 20.00,
                'ClassificationID' => 3, // Snacks
                'IsDeleted' => false
            ],
            [
                'ItemName' => 'Notebook',
                'UnitPrice' => 30.00,
                'ClassificationID' => 4, // School Supplies
                'IsDeleted' => false
            ],
        ];

        foreach ($items as $item) {
            DB::table('items')->insert($item);
        }
    }
} 