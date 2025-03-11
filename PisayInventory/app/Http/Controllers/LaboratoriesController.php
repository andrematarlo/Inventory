<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use Illuminate\Http\Request;

class LaboratoriesController extends Controller
{
    public function index()
    {
        $laboratories = Laboratory::withTrashed()->get();
        
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
        $laboratory = Laboratory::findOrFail($id);
        
        // Get user permissions for laboratories
        $userPermissions = (object) [
            'CanView' => true,  // You should replace these with actual permission checks
            'CanAdd' => true,
            'CanEdit' => true,
            'CanDelete' => true
        ];

        return view('laboratories.edit', compact('laboratory', 'userPermissions'));
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

        $laboratory = Laboratory::findOrFail($id);
        
        $laboratory->update([
            'laboratory_name' => $request->laboratory_name,
            'location' => $request->location,
            'capacity' => $request->capacity,
            'status' => $request->status,
            'description' => $request->description,
            'updated_at' => now(),
            'ModifiedById' => auth()->id()
        ]);

        return redirect()->route('laboratories.index')
            ->with('success', 'Laboratory updated successfully.');
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

        // Generate a unique laboratory ID (format: LAB-YYYYMMDD-XXXX)
        $date = now()->format('Ymd');
        $lastLab = Laboratory::whereDate('created_at', now())
            ->orderBy('laboratory_id', 'desc')
            ->first();
        
        $sequence = '0001';
        if ($lastLab) {
            $lastSequence = substr($lastLab->laboratory_id, -4);
            $sequence = str_pad((int)$lastSequence + 1, 4, '0', STR_PAD_LEFT);
        }
        
        $laboratoryId = "LAB-{$date}-{$sequence}";

        // Create the laboratory with the generated ID
        Laboratory::create([
            'laboratory_id' => $laboratoryId,
            'laboratory_name' => $request->laboratory_name,
            'location' => $request->location,
            'capacity' => $request->capacity,
            'status' => $request->status,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
            'CreatedById' => auth()->id() // Add this if you're tracking who created
        ]);

        return redirect()->route('laboratories.index')->with('success', 'Laboratory added successfully.');
    }
} 