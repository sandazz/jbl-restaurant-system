<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;

class TokenToTableService
{
    /**
     * Transfer a token (on_hold) order to a physical table.
     *
     * Clears the token_number, assigns the table, sets status to 'confirmed',
     * and marks the table as occupied — all inside a single transaction.
     */
    public function transferToTable(Order $order, int $tableId): Order
    {
        DB::transaction(function () use ($order, $tableId) {
            $table = RestaurantTable::lockForUpdate()->findOrFail($tableId);

            $order->token_number = null;
            $order->table_id     = $table->id;
            $order->status       = 'confirmed';
            $order->save();

            $table->markOccupied();
        });

        return $order->fresh(['table', 'items']);
    }

    /**
     * Save an order as a named token without assigning it to any table.
     * The cashier supplies a token_number string (e.g. "T-42").
     */
    public function holdAsToken(Order $order, string $tokenNumber): Order
    {
        $order->token_number = $tokenNumber;
        $order->table_id     = null;
        $order->status       = 'hold';
        $order->save();

        return $order->fresh();
    }
}
