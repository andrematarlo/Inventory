<?php

namespace App\Observers;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MenuItemObserver
{
    /**
     * Handle the MenuItem "updated" event.
     */
    public function updated(MenuItem $menuItem): void
    {
        // If this is a regular menu item (not a value meal), update all value meals that include it
        if (!$menuItem->IsValueMeal) {
            $this->updateValueMealsContainingItem($menuItem);
        }
    }

    /**
     * Update the stock of all value meals that contain the given menu item
     */
    private function updateValueMealsContainingItem(MenuItem $menuItem): void
    {
        try {
            DB::beginTransaction();

            // Get all value meals that include this item
            $valueMeals = MenuItem::where('IsValueMeal', true)
                ->whereHas('valueMealItems', function ($query) use ($menuItem) {
                    $query->where('menu_item_id', $menuItem->MenuItemID);
                })
                ->with(['valueMealItems.menuItem'])
                ->get();

            foreach ($valueMeals as $valueMeal) {
                // Calculate the maximum possible meals based on current stock of all items
                $maxMeals = PHP_INT_MAX;
                $stockDetails = [];

                foreach ($valueMeal->valueMealItems as $valueMealItem) {
                    $includedItem = $valueMealItem->menuItem;
                    $requiredQuantity = $valueMealItem->quantity;
                    $availableStock = $includedItem->StocksAvailable;
                    $possibleMeals = floor($availableStock / $requiredQuantity);
                    
                    $stockDetails[] = [
                        'item' => $includedItem->ItemName,
                        'required' => $requiredQuantity,
                        'available' => $availableStock,
                        'possible' => $possibleMeals
                    ];
                    
                    $maxMeals = min($maxMeals, $possibleMeals);
                }

                // Update the value meal's stock
                $oldStock = $valueMeal->StocksAvailable;
                $valueMeal->StocksAvailable = $maxMeals;
                $valueMeal->save();

                Log::info('Updated value meal stock:', [
                    'value_meal_id' => $valueMeal->MenuItemID,
                    'value_meal_name' => $valueMeal->ItemName,
                    'old_stock' => $oldStock,
                    'new_stock' => $maxMeals,
                    'stock_details' => $stockDetails
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating value meal stocks:', [
                'error' => $e->getMessage(),
                'menu_item_id' => $menuItem->MenuItemID,
                'menu_item_name' => $menuItem->ItemName
            ]);
        }
    }
} 