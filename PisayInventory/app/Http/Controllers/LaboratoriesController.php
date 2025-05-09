<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaboratoriesController extends Controller
{
    public function index()
    {
        $userRole = DB::table('useraccount')
            ->where('UserAccountID', Auth::id())
            ->value('role');

        // Strict filtering except for Admin
        if ($userRole && $userRole !== 'Admin') {
            $laboratories = Laboratory::withTrashed()
                ->where('role', trim($userRole))
                ->orderBy('laboratory_name')
                ->get();
        } else {
            $laboratories = Laboratory::withTrashed()
                ->orderBy('laboratory_name')
                ->get();
        }

        // Get user permissions for laboratories
        $userPermissions = (object) [
            'CanView' => true,  // You should replace these with actual permission checks
            'CanAdd' => true,
            'CanEdit' => true,
            'CanDelete' => true
        ];

        return view('laboratories.index', compact('laboratories', 'userPermissions'));
    }

    public function show($id)
    {
        $laboratory = Laboratory::withTrashed()->findOrFail($id);
        
        // Get user permissions for laboratories
        $userPermissions = (object) [
            'CanView' => true,  // You should replace these with actual permission checks
            'CanAdd' => true,
            'CanEdit' => true,
            'CanDelete' => true
        ];

        return view('laboratories.show', compact('laboratory', 'userPermissions'));
    }

    public function edit($id)
    {
        try {
            // Find the laboratory by ID
            $laboratory = Laboratory::where('laboratory_id', $id)->firstOrFail();
            
            // Get user permissions for laboratories
            $userPermissions = (object) [
                'CanView' => true,
                'CanAdd' => true,
                'CanEdit' => true,
                'CanDelete' => true
            ];

            return view('laboratories.edit', compact('laboratory', 'userPermissions'));
        } catch (\Exception $e) {
            return redirect()->route('laboratories.index')
                ->with('error', 'Laboratory not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'laboratory_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|string',
            'description' => 'nullable|string',
        ]);

        try {
            // Find the laboratory by ID
            $laboratory = Laboratory::where('laboratory_id', $id)->firstOrFail();
            
            $laboratory->update([
                'laboratory_name' => $request->laboratory_name,
                'location' => $request->location,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'description' => $request->description,
                'updated_at' => now(),
                'ModifiedById' => auth()->id() ?? 1
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laboratory updated successfully.'
                ]);
            }

            // Return with a session flash message for the SweetAlert2
            return redirect()->route('laboratories.index')
                ->with('success', 'Laboratory updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update laboratory: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to update laboratory: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'laboratory_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Verify the ID is unique before creating
        if (Laboratory::withTrashed()->where('laboratory_id', $request->laboratory_id)->exists()) {
            // If duplicate found, generate a new unique ID
            $laboratoryId = $this->getNextUniqueId();
        } else {
            $laboratoryId = $request->laboratory_id;
        }

        try {
            // Create the laboratory with the verified unique ID
            Laboratory::create([
                'laboratory_id' => $laboratoryId,
                'laboratory_name' => $request->laboratory_name,
                'location' => $request->location,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'description' => $request->description,
                'created_by' => auth()->id() ?? 1,
                'role' => DB::table('useraccount')->where('UserAccountID', auth()->id())->value('role'),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laboratory added successfully.',
                    'laboratory' => ['laboratory_id' => $laboratoryId]
                ]);
            }

            return redirect()->route('laboratories.index')->with('success', 'Laboratory added successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create laboratory. ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to create laboratory. ' . $e->getMessage());
        }
    }

    private function getNextUniqueId()
    {
        // Get today's date in YYYYMMDD format
        $today = date('Ymd');
        
        // Find the highest sequence number for today's date
        $lastLab = Laboratory::withTrashed()
            ->where('laboratory_id', 'LIKE', "LAB-{$today}-%")
            ->orderBy('laboratory_id', 'desc')
            ->first();
        
        $sequence = '0001';
        if ($lastLab) {
            // Extract the sequence number from the last laboratory ID
            $lastSequence = substr($lastLab->laboratory_id, -4);
            if (is_numeric($lastSequence)) {
                $sequence = str_pad((int)$lastSequence + 1, 4, '0', STR_PAD_LEFT);
            }
        }
        
        $laboratoryId = "LAB-{$today}-{$sequence}";

        // Verify uniqueness
        while (Laboratory::withTrashed()->where('laboratory_id', $laboratoryId)->exists()) {
            $sequence = str_pad((int)$sequence + 1, 4, '0', STR_PAD_LEFT);
            $laboratoryId = "LAB-{$today}-{$sequence}";
        }

        return $laboratoryId;
    }

    public function getNextId()
    {
        // Get today's date in YYYYMMDD format
        $today = date('Ymd');
        
        // Find the highest sequence number for today's date
        $lastLab = Laboratory::withTrashed()
            ->where('laboratory_id', 'LIKE', "LAB-{$today}-%")
            ->orderBy('laboratory_id', 'desc')
            ->first();
        
        $sequence = '0001';
        if ($lastLab) {
            // Extract the sequence number from the last laboratory ID
            $lastSequence = substr($lastLab->laboratory_id, -4);
            if (is_numeric($lastSequence)) {
                $sequence = str_pad((int)$lastSequence + 1, 4, '0', STR_PAD_LEFT);
            }
        }
        
        $laboratoryId = "LAB-{$today}-{$sequence}";

        // Verify uniqueness
        while (Laboratory::withTrashed()->where('laboratory_id', $laboratoryId)->exists()) {
            $sequence = str_pad((int)$sequence + 1, 4, '0', STR_PAD_LEFT);
            $laboratoryId = "LAB-{$today}-{$sequence}";
        }

        return response()->json(['next_id' => $laboratoryId]);
    }

    public function destroy($id)
    {
        try {
            $laboratory = Laboratory::findOrFail($id);
            
            // Check if laboratory has related equipment
            if ($laboratory->equipment()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete laboratory because it has associated equipment.'
                ], 422);
            }

            // Check if laboratory has active reservations
            if ($laboratory->activeReservations()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete laboratory because it has active reservations.'
                ], 422);
            }

            $laboratory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Laboratory deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the laboratory.'
            ], 500);
        }
    }

    /**
     * Restore the specified soft-deleted laboratory.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            // Log the ID we're trying to restore
            \Log::info('Attempting to restore laboratory with ID: ' . $id);
            
            // Check if the record exists at all (even with trashed)
            $exists = Laboratory::withTrashed()->where('laboratory_id', $id)->exists();
            \Log::info('Laboratory exists in database (including trashed): ' . ($exists ? 'Yes' : 'No'));
            
            if (!$exists) {
                // Try to find any laboratory to see if the model works
                $anyLab = Laboratory::withTrashed()->first();
                \Log::info('Any laboratory found: ' . ($anyLab ? 'Yes, ID: ' . $anyLab->laboratory_id : 'No'));
                
                return response()->json([
                    'success' => false,
                    'message' => 'Laboratory not found with ID: ' . $id
                ], 404);
            }
            
            // Get the laboratory directly
            $laboratory = Laboratory::withTrashed()->where('laboratory_id', $id)->first();
            
            if (!$laboratory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laboratory found in exists() but not in first()'
                ], 500);
            }
            
            \Log::info('Laboratory found, deleted_at: ' . ($laboratory->deleted_at ? $laboratory->deleted_at : 'Not deleted'));
            
            // Restore it
            $laboratory->restore();
            \Log::info('Laboratory restored');
            
            return response()->json([
                'success' => true,
                'message' => 'Laboratory restored successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception in restore: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore laboratory: ' . $e->getMessage()
            ], 500);
        }
    }
} 