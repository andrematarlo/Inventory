<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemClassification;
use Illuminate\Http\Request;

class POSKioskController extends Controller
{
    public function index()
    {
        // Get all active classifications
        $classifications = ItemClassification::where('IsDeleted', false)
                                          ->orderBy('ClassificationName')
                                          ->get();

        // Get items for the first classification
        $items = collect();
        if ($classifications->isNotEmpty()) {
            $items = Item::where('IsDeleted', false)
                        ->where('ClassificationID', $classifications->first()->ClassificationID)
                        ->get();
        }

        return view('pos.kiosk.index', compact('classifications', 'items'));
    }

    public function getItemsByClassification($classificationId)
    {
        $items = Item::where('IsDeleted', false)
                    ->where('ClassificationID', $classificationId)
                    ->get();

        return response()->json($items);
    }
} 