<?php

namespace App\Http\Controllers;

use App\Http\Requests\QrWebhookRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Support\Str;

class QrWebhookController extends Controller
{
    /**
     * Receive a QR scan payload and either create a new order for the table
     * or append items to the existing active order.
     *
     * POST /api/qr/scan
     */
    public function handle(QrWebhookRequest $request)
    {
        $table = RestaurantTable::findOrFail($request->table_id);

        $order = $table->activeOrder()->first()
            ?? $this->createFreshOrder($table);

        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                if (!$product) {
                    continue;
                }

                $existing = $order->items()
                    ->where('product_id', $product->id)
                    ->first();

                if ($existing) {
                    $existing->quantity += $itemData['quantity'];
                    $existing->subtotal  = $existing->unit_price * $existing->quantity;
                    $existing->save();
                } else {
                    $order->items()->create([
                        'product_id'     => $product->id,
                        'product_name'   => $product->name,
                        'unit_price'     => $product->selling_price ?? $product->price,
                        'quantity'       => $itemData['quantity'],
                        'subtotal'       => ($product->selling_price ?? $product->price) * $itemData['quantity'],
                        'kitchen_notes'  => $itemData['notes'] ?? null,
                        'is_bar_item'    => false,
                    ]);
                }
            }

            $this->recalculateTotals($order);
        }

        return response()->json([
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'table_id'     => $table->id,
            'items_count'  => $order->items()->count(),
            'total'        => $order->total,
        ]);
    }

    private function createFreshOrder(RestaurantTable $table): Order
    {
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'table_id'     => $table->id,
            'order_type'   => 'dine_in',
            'status'       => 'pending',
            'subtotal'     => 0,
            'discount_amount' => 0,
            'tax_amount'   => 0,
            'total'        => 0,
        ]);

        $table->markOccupied();

        return $order;
    }

    private function recalculateTotals(Order $order): void
    {
        $subtotal = $order->items()->sum(\DB::raw('unit_price * quantity'));
        $order->subtotal = $subtotal;
        $order->total    = $subtotal - $order->discount_amount + $order->tax_amount;
        $order->save();
    }
}
