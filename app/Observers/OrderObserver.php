<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\InventoryDeductionService;

class OrderObserver
{
    public function __construct(private readonly InventoryDeductionService $deduction) {}

    public function updated(Order $order): void
    {
        // Trigger raw-material deductions exactly once when the order is marked completed
        if ($order->wasChanged('status') && $order->status === 'completed') {
            $this->deduction->deductForOrder($order);
        }
    }
}
