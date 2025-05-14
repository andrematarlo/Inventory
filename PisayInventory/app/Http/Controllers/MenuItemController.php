<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\Classification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MenuItemController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::with('classification')->get();
        $regularMenuItems = MenuItem::where('IsValueMeal', false)
            ->where('IsDeleted', false)
            ->where('IsAvailable', true)
            ->get();
        $categories = Classification::where('IsDeleted', false)->get();
        $unitOfMeasures = \App\Models\UnitOfMeasure::where('IsDeleted', false)->get();
        
        return view('pos.menu-items.index', compact('menuItems', 'regularMenuItems', 'categories', 'unitOfMeasures'));
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
                'StocksAvailable' => 'required_if:IsValueMeal,0|integer|min:0',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'IsValueMeal' => 'boolean',
                'UnitOfMeasureID' => 'nullable|exists:unitofmeasure,UnitOfMeasureId',
                'value_meal_items' => 'required_if:IsValueMeal,1|array|min:1',
                'value_meal_items.*.menu_item_id' => 'required_with:value_meal_items|exists:menu_items,MenuItemID',
                'value_meal_items.*.quantity' => 'required_with:value_meal_items|integer|min:1'
            ]);

            $menuItem = new MenuItem();
            $menuItem->ItemName = $validated['ItemName'];
            $menuItem->Description = $validated['Description'] ?? null;
            $menuItem->Price = $validated['Price'];
            $menuItem->ClassificationId = $validated['ClassificationId'];
            $menuItem->IsAvailable = true;
            $menuItem->IsDeleted = false;
            $menuItem->IsValueMeal = $request->boolean('IsValueMeal');
            $menuItem->UnitOfMeasureID = $validated['UnitOfMeasureID'] ?? 1;

            // Handle stocks for value meals vs regular items
            if (!$menuItem->IsValueMeal) {
                $menuItem->StocksAvailable = $validated['StocksAvailable'];
            } else {
                // For value meals, calculate the maximum possible meals based on included items
                $maxMeals = PHP_INT_MAX;
                foreach ($request->value_meal_items as $item) {
                    $includedItem = MenuItem::findOrFail($item['menu_item_id']);
                    if ($includedItem->IsValueMeal) {
                        throw new \Exception("Cannot include a value meal inside another value meal: {$includedItem->ItemName}");
                    }
                    $possibleMeals = floor($includedItem->StocksAvailable / $item['quantity']);
                    $maxMeals = min($maxMeals, $possibleMeals);
                }
                $menuItem->StocksAvailable = $maxMeals;
            }

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $menuItem->image_path = $imagePath;
            }

            $menuItem->save();

            // If this is a value meal, create the value meal items
            if ($menuItem->IsValueMeal && $request->has('value_meal_items')) {
                foreach ($request->value_meal_items as $item) {
                    $menuItem->valueMealItems()->create([
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }

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
            Log::error('Menu Item Creation Error: ' . $e->getMessage());
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

            // Basic validation rules
            $rules = [
                'ItemName' => 'required|string|max:255',
                'Description' => 'nullable|string',
                'Price' => 'required|numeric|min:0',
                'ClassificationId' => 'required|exists:classification,ClassificationId',
                'UnitOfMeasureID' => 'nullable|exists:unitofmeasure,UnitOfMeasureId',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ];

            // Add stock validation only for non-value meals
            if (!$request->has('IsValueMeal')) {
                $rules['StocksAvailable'] = 'required|integer|min:0';
            }

            $validated = $request->validate($rules);
            
            $menuItem->ItemName = $validated['ItemName'];
            $menuItem->Description = $validated['Description'] ?? null;
            $menuItem->Price = $validated['Price'];
            $menuItem->ClassificationId = $validated['ClassificationId'];
            $menuItem->UnitOfMeasureID = $validated['UnitOfMeasureID'];
            $menuItem->IsValueMeal = $request->has('IsValueMeal');

            // Handle stocks for value meals vs regular items
            if (!$menuItem->IsValueMeal) {
                $menuItem->StocksAvailable = $validated['StocksAvailable'];
            } else if ($request->has('value_meal_items')) {
                // Only recalculate stock if value meal items are being updated
                $maxMeals = PHP_INT_MAX;
                foreach ($request->value_meal_items as $item) {
                    $includedItem = MenuItem::findOrFail($item['menu_item_id']);
                    if ($includedItem->IsValueMeal) {
                        throw new \Exception("Cannot include a value meal inside another value meal: {$includedItem->ItemName}");
                    }
                    $possibleMeals = floor($includedItem->StocksAvailable / $item['quantity']);
                    $maxMeals = min($maxMeals, $possibleMeals);
                }
                $menuItem->StocksAvailable = $maxMeals;
            }

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                    Storage::disk('public')->delete($menuItem->image_path);
                }
                
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $menuItem->image_path = $imagePath;
            }

            $menuItem->save();

            // Update value meal items only if they are included in the request
            if ($menuItem->IsValueMeal && $request->has('value_meal_items')) {
                // Delete existing value meal items
                $menuItem->valueMealItems()->delete();
                
                // Create new value meal items
                foreach ($request->value_meal_items as $item) {
                    $menuItem->valueMealItems()->create([
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Menu item updated successfully',
                'data' => $menuItem
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating menu item:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu item: ' . $e->getMessage()
            ], 500);
        }
    }
} 