<?php

namespace App\Services;

use App\Models\ClerkBalancing;
use App\Models\Order;

class ShiftBalancingService
{
    /** Denomination face values in ascending order */
    private const DENOMINATION_MAP = [
        'note_5000' => 5000,
        'note_2000' => 2000,
        'note_1000' => 1000,
        'note_500'  => 500,
        'note_200'  => 200,
        'note_100'  => 100,
        'note_50'   => 50,
        'note_20'   => 20,
        'note_10'   => 10,
        'coin_5'    => 5,
        'coin_2'    => 2,
        'coin_1'    => 1,
        'coin_50c'  => 0.5,
    ];

    /**
     * Open a new shift for the given cashier.
     */
    public function openShift(int $userId, float $openingAmount = 0): ClerkBalancing
    {
        return ClerkBalancing::create([
            'user_id'        => $userId,
            'shift_start'    => now(),
            'opening_amount' => $openingAmount,
            'status'         => 'open',
        ]);
    }

    /**
     * Close a shift.
     *
     * 1. Calculates physical cash total from submitted denomination counts.
     * 2. Queries expected cash & card sales for orders stamped with this shift_id.
     * 3. Calculates variance = physical_cash_total − expected_cash_total.
     * 4. Flags variance_type as shortage | excess | balanced.
     * 5. Saves and returns the closed record.
     */
    public function closeShift(ClerkBalancing $balancing, array $denominations, ?string $notes = null): ClerkBalancing
    {
        $physicalCash = $this->calculatePhysicalCash($denominations);

        $shiftEnd = now();

        // cash_amount/card_amount hold the actual tender split for every payment
        // method (including split/mixed), so summing them covers all orders
        // stamped with this shift regardless of payment_method.
        $baseQuery = Order::where('shift_id', $balancing->id)
            ->where('status', 'completed');

        $expectedCash = (clone $baseQuery)->sum('cash_amount');
        $expectedCard = (clone $baseQuery)->sum('card_amount');

        $expectedTotal = $balancing->opening_amount + $expectedCash;
        $rawVariance  = $physicalCash - $expectedTotal;
        $varianceType = match (true) {
            $rawVariance < 0 => 'shortage',
            $rawVariance > 0 => 'excess',
            default          => 'balanced',
        };

        $balancing->fill(array_merge($denominations, [
            'physical_cash_total' => $physicalCash,
            'expected_cash_total' => $expectedCash,
            'expected_card_total' => $expectedCard,
            'variance'            => abs($rawVariance),
            'variance_type'       => $varianceType,
            'shift_end'           => $shiftEnd,
            'notes'               => $notes,
            'status'              => 'closed',
        ]));

        $balancing->save();

        return $balancing;
    }

    /** Sum denomination counts × face values. */
    public function calculatePhysicalCash(array $denominations): float
    {
        $total = 0.0;

        foreach (self::DENOMINATION_MAP as $field => $value) {
            $total += ($denominations[$field] ?? 0) * $value;
        }

        return round($total, 2);
    }
}
