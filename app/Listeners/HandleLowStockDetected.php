<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use Illuminate\Support\Facades\Log;

class HandleLowStockDetected
{
    public function handle(LowStockDetected $event): void
    {
        $inventory = $event->inventory;

        Log::warning('Low stock detected', [
            'inventory_id'     => $inventory->id,
            'name'             => $inventory->name,
            'current_balance'  => $inventory->current_balance,
            'reorder_level'    => $inventory->reorder_level,
            'unit'             => $inventory->unit,
        ]);

        // TODO: swap Log::warning for a Notification/Mail when notification channels are configured
    }
}
