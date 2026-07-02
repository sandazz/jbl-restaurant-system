<?php

namespace App\Console\Commands;

use App\Models\ClerkBalancing;
use App\Models\Order;
use Illuminate\Console\Command;

class RecalculateShiftTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-shift-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill cash_amount/card_amount/shift_id on completed orders (net of change given) and recalculate every closed shift\'s expected_cash_total, expected_card_total, and variance.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Backfilling order cash_amount / card_amount / shift_id...');

        $orders = Order::where('status', 'completed')->get();
        $bar = $this->output->createProgressBar($orders->count());

        foreach ($orders as $order) {
            $paid = (float) $order->amount_paid;
            $change = (float) ($order->change_amount ?? 0);
            $net = $paid - $change;

            $cash = 0;
            $card = 0;
            if ($order->payment_method === 'cash') {
                $cash = $net;
            } elseif (in_array($order->payment_method, ['card', 'bank_transfer'], true)) {
                $card = $net;
            } else {
                // mixed/split without a stored per-tender breakdown: best-effort as cash
                $cash = $net;
            }

            $shiftId = $order->shift_id;
            if ($shiftId === null) {
                $shiftId = ClerkBalancing::where('user_id', $order->user_id)
                    ->where(function ($q) use ($order) {
                        $q->whereNull('shift_end')->orWhere('shift_end', '>=', $order->created_at);
                    })
                    ->where('shift_start', '<=', $order->created_at)
                    ->orderBy('shift_start')
                    ->value('id');
            }

            $order->timestamps = false;
            $order->cash_amount = $cash;
            $order->card_amount = $card;
            $order->shift_id = $shiftId;
            $order->save();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Recalculating closed shift totals...');

        $shifts = ClerkBalancing::where('status', 'closed')->get();

        foreach ($shifts as $shift) {
            $expectedCash = Order::where('shift_id', $shift->id)
                ->where('status', 'completed')
                ->sum('cash_amount');

            $expectedCard = Order::where('shift_id', $shift->id)
                ->where('status', 'completed')
                ->sum('card_amount');

            $expectedTotal = $shift->opening_amount + $expectedCash;
            $rawVariance = $shift->physical_cash_total - $expectedTotal;
            $varianceType = match (true) {
                $rawVariance < 0 => 'shortage',
                $rawVariance > 0 => 'excess',
                default => 'balanced',
            };

            $shift->update([
                'expected_cash_total' => $expectedCash,
                'expected_card_total' => $expectedCard,
                'variance' => abs($rawVariance),
                'variance_type' => $varianceType,
            ]);

            $this->line("Shift #{$shift->id}: cash={$expectedCash} card={$expectedCard} variance={$shift->variance} ({$varianceType})");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
