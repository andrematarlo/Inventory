<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\Classification;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::with('classification')->get();
        $categories = Classification::all();
        
        return view('pos.menu-items.index', compact('menuItems', 'categories'));
    }

    public function destroy($id)
    {
        try {
            $menuItem = MenuItem::findOrFail($id);
            
            // Delete image if exists
            if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            
            $menuItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menu item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting menu item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new menu item.
     */
    public function create()
    {
        $categories = Classification::all();
        return view('pos.menu-items.create', compact('categories'));
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ItemName' => 'required|string|max:255',
                'Description' => 'nullable|string',
                'Price' => 'required|numeric|min:0',
                'StocksAvailable' => 'required|integer|min:0',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $menuItem = new MenuItem();
            $menuItem->ItemName = $validated['ItemName'];
            $menuItem->Description = $validated['Description'] ?? null;
            $menuItem->Price = $validated['Price'];
            $menuItem->StocksAvailable = $validated['StocksAvailable'];
            $menuItem->ClassificationId = $validated['ClassificationId'];
            $menuItem->IsAvailable = true;
            $menuItem->IsDeleted = false;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $menuItem->image_path = $imagePath;
            }

            $menuItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Menu item created successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Menu Item Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating menu item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $categories = Classification::all();
        
        return view('pos.menu-items.edit', compact('menuItem', 'categories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $menuItem = MenuItem::findOrFail($id);

            $validated = $request->validate([
                'ItemName' => 'required|string|max:255',
                'Description' => 'nullable|string',
                'Price' => 'required|numeric|min:0',
                'StocksAvailable' => 'required|integer|min:0',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $menuItem->ItemName = $validated['ItemName'];
            $menuItem->Description = $validated['Description'];
            $menuItem->Price = $validated['Price'];
            $menuItem->StocksAvailable = $validated['StocksAvailable'];
            $menuItem->ClassificationId = $validated['ClassificationId'];

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                    Storage::disk('public')->delete($menuItem->image_path);
                }
                
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $menuItem->image_path = $imagePath;
            }

            $menuItem->save();

            return redirect()
                ->route('pos.menu-items.index')
                ->with('success', 'Menu item updated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating menu item: ' . $e->getMessage());
        }
    }
} 