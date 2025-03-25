<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class POSController extends Controller
{
    /**
     * Update stock for a menu item
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStock(Request $request)
    {
        // Validate the request
        $request->validate([
            'item_id' => 'required|integer|exists:menu_items,MenuItemID',
            'quantity' => 'required|integer|min:1'
        ]);
        
        // Find the menu item
        $item = MenuItem::find($request->item_id);
        
        // Update the stock with correct column name
        $newStock = $item->available_stock + $request->quantity;
        $item->available_stock = $newStock;
        $item->save();
        
        // Log the stock update (optional)
        // You might want to add a stock transaction log here
        
        // Return success response
        return response()->json([
            'success' => true,
            'message' => "{$request->quantity} items added to {$item->ItemName} stock",
            'new_stock' => $newStock
        ]);
    }

    public function addMenuItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'classification_id' => 'required|exists:classification,ClassificationId',
            'stocks_available' => 'required|integer|min:0',
            'description' => 'nullable|string|max:255',
            'item_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            DB::beginTransaction();
            
            // Handle image upload if present
            $imagePath = null;
            if ($request->hasFile('item_image')) {
                $imagePath = $request->file('item_image')->store('menu-items', 'public');
            }
            
            // Create menu item
            $menuItemId = DB::table('menu_items')->insertGetId([
                'ItemName' => $request->item_name,
                'Description' => $request->description,
                'Price' => $request->price,
                'ClassificationID' => $request->classification_id,
                'IsAvailable' => true,
                'IsDeleted' => false,
                'StocksAvailable' => $request->stocks_available,
                'image_path' => $imagePath,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Menu item added successfully!',
                'item' => [
                    'id' => $menuItemId,
                    'name' => $request->item_name,
                    'price' => $request->price,
                    'description' => $request->description,
                    'stocks' => $request->stocks_available,
                    'image_path' => $imagePath ? asset('storage/' . $imagePath) : null
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add menu item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        \Log::info('Attempting to delete menu item', ['id' => $id]);
        
        try {
            $menuItem = DB::table('menu_items')->where('MenuItemID', $id)->first();
            
            \Log::info('Menu item found', ['menuItem' => $menuItem]);
            
            if (!$menuItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu item not found'
                ], 404);
            }

            // Soft delete by updating IsDeleted flag
            DB::table('menu_items')
                ->where('MenuItemID', $id)
                ->update([
                    'IsDeleted' => 1,
                    'updated_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Menu item deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete menu item', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
} 