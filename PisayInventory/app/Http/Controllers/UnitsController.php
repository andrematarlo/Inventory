<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UnitOfMeasure;

class UnitsController extends Controller
{
    public function getUserPermissions($module = null)
    {
        try {
            if (!Auth::check()) {
                return (object)[
                    'CanAdd' => false,
                    'CanEdit' => false,
                    'CanDelete' => false,
                    'CanView' => false
                ];
            }

            return parent::getUserPermissions('Units');

        } catch (\Exception $e) {
            Log::error('Error getting permissions: ' . $e->getMessage());
            return (object)[
                'CanAdd' => false,
                'CanEdit' => false,
                'CanDelete' => false,
                'CanView' => false
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $unit = UnitOfMeasure::findOrFail($id);
            
            // Check if unit is being used
            if ($unit->items()->count() > 0) {
                return back()->with('error', 'Cannot delete unit because it is being used by ' . $unit->items()->count() . ' items.');
            }

            $unit->delete();
            
            // Just redirect back without any message
            return redirect()->back();
            
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the unit.');
        }
    }

    public function restore($id)
    {
        try {
            $unit = UnitOfMeasure::withTrashed()->findOrFail($id);
            $unit->restore();
            
            // Just redirect back without any message
            return redirect()->back();
            
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while restoring the unit.');
        }
    }
} 