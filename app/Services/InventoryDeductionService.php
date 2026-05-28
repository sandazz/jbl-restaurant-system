<?php

namespace App\Services;

use App\Events\LowStockDetected;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class InventoryDeductionService
{
    /**
     * Atomically deduct raw-material stock for every item in a completed order.
     *
     * Loops through each OrderItem → loads the Product's ingredient BOM →
     * deducts (quantity_required × item_quantity) from the Inventory balance
     * inside a single DB transaction. Fires LowStockDetected when balance
     * drops at or below reorder_level.
     */
    public function deductForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->loadMissing('items.product.ingredients');

            foreach ($order->items as $item) {
                if (!$item->product) {
                    continue;
                }

                foreach ($item->product->ingredients as $ingredient) {
                    $quantityToDeduct = $ingredient->pivot->quantity_required * $item->quantity;

                    // Pessimistic lock prevents race conditions on concurrent orders
                    $inventory = Inventory::lockForUpdate()->find($ingredient->id);

                    if (!$inventory) {
                        continue;
                    }

                    $inventory->current_balance = max(0, $inventory->current_balance - $quantityToDeduct);
                    $inventory->save();

                    if ($inventory->isLowStock()) {
                        event(new LowStockDetected($inventory));
                    }
                }
            }
        });
    }

    /**
     * Manually deduct a specific quantity from a single inventory item.
     * Useful for wastage and standalone adjustments.
     */
    public function deductSingle(Inventory $inventory, float $quantity, string $reason = 'manual'): void
    {
        DB::transaction(function () use ($inventory, $quantity, $reason) {
            $inventory = Inventory::lockForUpdate()->find($inventory->id);
            $inventory->current_balance = max(0, $inventory->current_balance - $quantity);
            $inventory->save();

            if ($inventory->isLowStock()) {
                event(new LowStockDetected($inventory));
            }
        });
    }
}
