<?php

namespace App\Services;

use App\Events\LowStockDetected;
use App\Models\Inventory;
use App\Models\InventoryMovement;
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
     * drops at or below reorder_level. Every deduction is logged to
     * inventory_movements for audit trail.
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

                    $this->deductSingle(
                        $ingredient,
                        $quantityToDeduct,
                        reason: 'sale',
                        source: 'order',
                        referenceType: Order::class,
                        referenceId: $order->id,
                    );
                }
            }
        });
    }

    /**
     * Deduct a specific quantity from a single inventory item and log it.
     * Used for order-driven sale deductions as well as manual/wastage adjustments.
     */
    public function deductSingle(
        Inventory $inventory,
        float $quantity,
        string $reason = 'manual',
        string $source = 'adjustment',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
    ): void {
        DB::transaction(function () use ($inventory, $quantity, $reason, $source, $referenceType, $referenceId, $notes) {
            $inventory = Inventory::lockForUpdate()->find($inventory->id);

            if (!$inventory) {
                return;
            }

            $inventory->current_balance = max(0, $inventory->current_balance - $quantity);
            $inventory->save();

            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'change_type' => 'decrease',
                'quantity' => $quantity,
                'balance_after' => $inventory->current_balance,
                'reason' => $reason,
                'source' => $source,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);

            if ($inventory->isLowStock()) {
                event(new LowStockDetected($inventory));
            }
        });
    }

    /**
     * Increase raw-material stock (e.g. purchasing / goods received) and log it.
     */
    public function restock(
        Inventory $inventory,
        float $quantity,
        string $reason = 'purchase',
        ?string $notes = null,
    ): void {
        DB::transaction(function () use ($inventory, $quantity, $reason, $notes) {
            $inventory = Inventory::lockForUpdate()->find($inventory->id);

            if (!$inventory) {
                return;
            }

            $inventory->current_balance += $quantity;
            $inventory->save();

            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'change_type' => 'increase',
                'quantity' => $quantity,
                'balance_after' => $inventory->current_balance,
                'reason' => $reason,
                'source' => 'restock',
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);
        });
    }
}
