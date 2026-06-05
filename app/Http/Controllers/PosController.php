<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TierDiscount;
use App\Models\ClerkBalancing;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $tables       = RestaurantTable::all()->load('activeOrder.items');
        $categories   = Category::where('status', 'active')->orderBy('sort_order')->get();
        $products     = Product::where('status', 'active')->get();
        $user = Auth::user();
        $modules = $user ? $user->role->modules()->get() : collect();
        $tierDiscounts = TierDiscount::activeMap(); // ['VIP' => 15.0, 'Moderate' => 10.0, ...]

        $hasOpenShift = ClerkBalancing::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        return view('modules.pos', [
            'tables'        => $tables,
            'categories'    => $categories,
            'products'      => $products,
            'modules'       => $modules,
            'tierDiscounts' => $tierDiscounts,
            'hasOpenShift'  => $hasOpenShift,
        ]);
    }

    public function getTables()
    {
        $tables = RestaurantTable::with('activeOrders.items')->get()->map(function ($table) {
            $activeOrders = $table->activeOrders;
            $firstOrder = $activeOrders->first();

            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'status' => $table->status,
                'section' => $table->section,
                'occupied_at' => $table->occupied_at,
                'has_order' => $activeOrders->count() > 0,
                'order_id' => $firstOrder?->id,
                'order_items_count' => $firstOrder?->items->count() ?? 0,
                'active_tokens' => $activeOrders->map(fn($order) => [
                    'order_id' => $order->id,
                    'token_number' => $order->token_number,
                    'order_number' => $order->order_number,
                    'items_count' => $order->items->count(),
                    'customer_name' => $order->customer_name,
                    'total' => (float) $order->total,
                ])->toArray(),
            ];
        });

        return response()->json($tables);
    }

    public function getProducts(Request $request)
    {
        $query = Product::where('status', 'active');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', $search);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $products = $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) ($product->selling_price ?? $product->price),
                'cost_price' => (float) ($product->cost_price ?? 0),
                'category_id' => $product->category_id,
                'barcode' => $product->barcode,
                'is_unlimited_stock' => $product->is_unlimited_stock,
                'quantity' => $product->quantity,
                'image' => $product->image,
            ];
        });

        return response()->json($products);
    }

    public function createOrder(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'table_id' => 'nullable|exists:restaurant_tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'order_type' => 'required|in:dine_in,takeaway,delivery,vip_room',
            'waiter_name' => 'nullable|string',
        ]);

        $tokenNumber = $this->generateTokenNumber();

        $order = Order::create([
            'order_number' => 'ORD-' . Str::random(8),
            'token_number' => $tokenNumber,
            'table_id' => $validated['table_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'user_id' => Auth::id(),
            'order_type' => $validated['order_type'],
            'waiter_name' => $validated['waiter_name'] ?? ($user?->name ?? 'Unknown'),
        ]);

        // Only mark table as occupied if it's not already
        if (!empty($validated['table_id'])) {
            $table = RestaurantTable::find($validated['table_id']);
            if ($table && $table->status !== 'occupied') {
                $table->update([
                    'status' => 'occupied',
                    'occupied_at' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'token_number' => $tokenNumber,
        ]);
    }

    public function getOrder(Order $order)
    {
        $order->load('items.product', 'table', 'customer');

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'token_number' => $order->token_number,
            'table_id' => $order->table_id,
            'table_number' => $order->table?->table_number,
            'order_type' => $order->order_type,
            'status' => $order->status,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer' => $order->customer,
            'live_bill_enabled' => $order->live_bill_enabled,
            'waiter_bill_printed_at' => $order->waiter_bill_printed_at,
            'subtotal' => (float) $order->subtotal,
            'discount_amount' => (float) $order->discount_amount,
            'tax_amount' => (float) $order->tax_amount,
            'total' => (float) $order->total,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                    'kitchen_notes' => $item->kitchen_notes,
                    'is_bar_item' => $item->is_bar_item,
                    'image' => $item->product?->image,
                ];
            }),
        ]);
    }

    public function addItem(Request $request, Order $order)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'kitchen_notes' => 'nullable|string',
            'is_bar_item' => 'nullable|boolean',
        ]);
        return DB::transaction(function () use ($validated, $order) {
            $product = Product::whereKey($validated['product_id'])->lockForUpdate()->first();
            $price = $product->selling_price ?? $product->price;
            $requestedQty = (int) $validated['quantity'];

            if (!$product->is_unlimited_stock && $product->quantity < $requestedQty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available',
                    'available' => $product->quantity,
                ], 422);
            }

            $existingItem = OrderItem::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $requestedQty;
                $existingItem->subtotal = $existingItem->unit_price * $existingItem->quantity;
                $existingItem->save();
                $item = $existingItem;
            } else {
                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $price,
                    'quantity' => $requestedQty,
                    'subtotal' => $price * $requestedQty,
                    'kitchen_notes' => $validated['kitchen_notes'] ?? null,
                    'is_bar_item' => $validated['is_bar_item'] ?? false,
                    'kot_sent_quantity' => 0,
                ]);
            }

            if (!$product->is_unlimited_stock) {
                $product->decrement('quantity', $requestedQty);
            }

            $this->updateOrderTotals($order);

            return response()->json([
                'success' => true,
                'item_id' => $item->id,
                'message' => $product->name . ' added to order',
                'product' => [
                    'id' => $product->id,
                    'is_unlimited_stock' => $product->is_unlimited_stock,
                    'quantity' => $product->fresh()->quantity,
                ],
            ]);
        });
    }

    public function removeItem(Request $request, Order $order, OrderItem $item)
    {
        return DB::transaction(function () use ($order, $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

            if ($product && !$product->is_unlimited_stock) {
                $product->increment('quantity', $item->quantity);
            }

            $item->delete();
            $this->updateOrderTotals($order);

            return response()->json([
                'success' => true,
                'message' => 'Item removed',
                'product' => $product ? [
                    'id' => $product->id,
                    'is_unlimited_stock' => $product->is_unlimited_stock,
                    'quantity' => $product->fresh()->quantity,
                ] : null,
            ]);
        });
    }

    public function updateItem(Request $request, Order $order, OrderItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'kitchen_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($order, $item, $validated) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            $newQuantity = (int) $validated['quantity'];
            $difference = $newQuantity - $item->quantity;

            if ($difference > 0 && $product && !$product->is_unlimited_stock) {
                if ($product->quantity < $difference) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not enough stock available',
                        'available' => $product->quantity,
                    ], 422);
                }
                $product->decrement('quantity', $difference);
            }

            if ($difference < 0 && $product && !$product->is_unlimited_stock) {
                $product->increment('quantity', abs($difference));
            }

            $item->update([
                'quantity' => $newQuantity,
                'subtotal' => $item->unit_price * $newQuantity,
                'kitchen_notes' => $validated['kitchen_notes'] ?? $item->kitchen_notes,
                'kot_sent_quantity' => min($item->kot_sent_quantity ?? 0, $newQuantity),
            ]);

            $this->updateOrderTotals($order);

            return response()->json([
                'success' => true,
                'message' => 'Item updated',
                'product' => $product ? [
                    'id' => $product->id,
                    'is_unlimited_stock' => $product->is_unlimited_stock,
                    'quantity' => $product->fresh()->quantity,
                ] : null,
            ]);
        });
    }

    public function holdOrder(Order $order)
    {
        $order->update(['status' => 'hold']);
        return response()->json(['success' => true, 'message' => 'Order held']);
    }

    public function completeOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $subtotal = $order->items->sum('subtotal');
        $discount = 0;

        if ($validated['discount_type'] === 'percentage') {
            $discount = ($subtotal * $validated['discount_value']) / 100;
        } elseif ($validated['discount_type'] === 'fixed') {
            $discount = $validated['discount_value'];
        }

        $total = $subtotal - $discount;

        $order->update([
            'status' => 'completed',
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => 0,
            'total' => $total,
            'printed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'table_number' => $order->table?->table_number,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'subtotal' => (float) $subtotal,
            'discount_amount' => (float) $discount,
            'total' => (float) $total,
            'items' => $order->items->map(fn($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
                'kitchen_notes' => $item->kitchen_notes,
            ]),
        ]);
    }

    public function printKot(Order $order)
    {
        $order->load('table');
        $pendingItems = $order->items()
            ->where('is_bar_item', false)
            ->where(function ($query) {
                $query->whereColumn('quantity', '>', 'kot_sent_quantity')
                      ->orWhereNull('kot_sent_quantity');
            })
            ->get();

        if ($pendingItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No new kitchen items to print.',
                'order_number' => $order->order_number,
                'token_number' => $order->token_number,
                'order_type' => $order->order_type,
                'table_number' => $order->table?->table_number,
                'table_name' => $order->table?->name,
                'items' => [],
            ]);
        }

        $order->update(['kot_printed_at' => now()]);
        $kitchenItems = $pendingItems->map(function ($item) {
            $sent = $item->kot_sent_quantity ?? 0;
            return [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => max(0, $item->quantity - $sent),
                'kitchen_notes' => $item->kitchen_notes,
            ];
        });

        foreach ($pendingItems as $item) {
            $item->update(['kot_sent_quantity' => $item->quantity]);
        }

        return response()->json([
            'success'      => true,
            'order_number' => $order->order_number,
            'token_number' => $order->token_number,
            'order_type'   => $order->order_type,
            'table_number' => $order->table?->table_number,
            'table_name'   => $order->table?->name,
            'items'        => $kitchenItems,
        ]);
    }

    public function printBot(Order $order)
    {
        $order->update(['bot_printed_at' => now()]);
        $barItems = $order->items->where('is_bar_item', true)->values();

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'items' => $barItems->map(fn($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'kitchen_notes' => $item->kitchen_notes,
            ]),
        ]);
    }

    public function getHeldOrders()
    {
        $orders = Order::where('status', 'hold')->with('table', 'items')->latest()->get();

        return response()->json($orders->map(fn($order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'token_number' => $order->token_number,
            'table_number' => $order->table?->table_number,
            'items_count' => $order->items->count(),
            'total' => (float) $order->total,
        ]));
    }

    private function generateTokenNumber(): string
    {
        $count = Order::whereDate('created_at', today())->count() + 1;
        return 'T-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    private function updateOrderTotals(Order $order)
    {
        $subtotal = $order->items->sum('subtotal');
        $order->update([
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'total' => $subtotal,
        ]);
    }

    public function updateCustomer(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id'    => 'nullable|integer|exists:customers,id',
            'customer_name'  => 'nullable|string',
            'customer_phone' => 'nullable|string',
        ]);

        $order->update([
            'customer_id'    => $validated['customer_id'] ?? null,
            'customer_name'  => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer details updated',
        ]);
    }

    public function printWaiterBill(Request $request, Order $order)
    {
        $order->load('items', 'table');
        $order->update(['waiter_bill_printed_at' => now()]);

        $subtotal      = $order->items->sum('subtotal');
        $discountType  = $request->input('discount_type');
        $discountValue = (float) $request->input('discount_value', 0);

        $discountAmount = 0;
        if ($discountType === 'percentage') {
            $discountAmount = round(($subtotal * $discountValue) / 100, 2);
        } elseif ($discountType === 'fixed') {
            $discountAmount = min($discountValue, $subtotal);
        }

        $total = max(0, $subtotal - $discountAmount);

        return response()->json([
            'success'         => true,
            'order_number'    => $order->order_number,
            'token_number'    => $order->token_number,
            'order_type'      => $order->order_type,
            'table_number'    => $order->table?->table_number,
            'table_name'      => $order->table?->name,
            'customer_name'   => $order->customer_name,
            'customer_phone'  => $order->customer_phone,
            'subtotal'        => (float) $subtotal,
            'discount_type'   => $discountType,
            'discount_value'  => $discountValue,
            'discount_amount' => (float) $discountAmount,
            'total'           => (float) $total,
            'items'           => $order->items->map(fn($item) => [
                'product_name'  => $item->product_name,
                'quantity'      => $item->quantity,
                'unit_price'    => (float) $item->unit_price,
                'subtotal'      => (float) $item->subtotal,
                'kitchen_notes' => $item->kitchen_notes,
            ]),
        ]);
    }

    public function toggleLiveBill(Request $request, Order $order)
    {
        $order->update(['live_bill_enabled' => !$order->live_bill_enabled]);

        return response()->json([
            'success' => true,
            'live_bill_enabled' => $order->live_bill_enabled,
            'message' => $order->live_bill_enabled ? 'Live bill enabled' : 'Live bill disabled',
        ]);
    }

    public function closeTable(Order $order)
    {
        $order->update(['status' => 'cancelled']);

        if ($order->table_id) {
            $table = RestaurantTable::find($order->table_id);
            if ($table) {
                // Only free the table if NO other active orders remain
                $remainingOrders = $table->activeOrders()->count();
                if ($remainingOrders === 0) {
                    $table->update([
                        'status' => 'available',
                        'occupied_at' => null,
                    ]);
                }
            }
        }
        return response()->json(['success' => true, 'message' => 'Table closed']);
    }

    public function payOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,bank_transfer,mixed,split',
            'amount_paid'    => 'required|numeric|min:0',
            'discount_type'  => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $order->load('items', 'table');
        $subtotal = $order->items->sum('subtotal');
        $discount = 0;

        if (($validated['discount_type'] ?? null) === 'percentage') {
            $discount = ($subtotal * $validated['discount_value']) / 100;
        } elseif (($validated['discount_type'] ?? null) === 'fixed') {
            $discount = $validated['discount_value'];
        }

        $total      = $subtotal - $discount;
        $amountPaid = $validated['amount_paid'];
        $change     = max(0, $amountPaid - $total);

        $order->update([
            'status'          => 'completed',
            'subtotal'        => $subtotal,
            'discount_amount' => $discount,
            'tax_amount'      => 0,
            'total'           => $total,
            'payment_method'  => $validated['payment_method'],
            'amount_paid'     => $amountPaid,
            'change_amount'   => $change,
            'printed_at'      => now(),
        ]);

        // Only update table status if no other active orders remain
        if ($order->table_id) {
            $table = RestaurantTable::find($order->table_id);
            if ($table) {
                $remainingOrders = $table->activeOrders()->count();
                if ($remainingOrders === 0) {
                    $table->update([
                        'status'      => 'available',
                        'occupied_at' => null,
                    ]);
                }
            }
        }

        return response()->json([
            'success'         => true,
            'order_number'    => $order->order_number,
            'token_number'    => $order->token_number,
            'order_type'      => $order->order_type,
            'table_number'    => $order->table?->table_number,
            'table_name'      => $order->table?->name,
            'customer_name'   => $order->customer_name,
            'customer_phone'  => $order->customer_phone,
            'subtotal'        => (float) $subtotal,
            'discount_type'   => $validated['discount_type'] ?? null,
            'discount_value'  => (float) ($validated['discount_value'] ?? 0),
            'discount_amount' => (float) $discount,
            'total'           => (float) $total,
            'payment_method'  => $validated['payment_method'],
            'amount_paid'     => (float) $amountPaid,
            'change_amount'   => (float) $change,
            'items'           => $order->items->map(fn($item) => [
                'product_name'  => $item->product_name,
                'quantity'      => $item->quantity,
                'unit_price'    => (float) $item->unit_price,
                'subtotal'      => (float) $item->subtotal,
                'kitchen_notes' => $item->kitchen_notes,
            ]),
        ]);
    }

    public function getTableOrders(RestaurantTable $table)
    {
        $orders = $table->orders()->with('items')->latest()->get();

        return response()->json($orders->map(fn($order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'customer_name' => $order->customer_name,
            'items_count' => $order->items->count(),
            'subtotal' => (float) $order->subtotal,
            'total' => (float) $order->total,
            'created_at' => $order->created_at,
        ]));
    }

    /** POST /pos/create-customer-quick — Create a customer from POS */
    public function createCustomerQuick(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'title' => 'nullable|in:Mr.,Miss,Master',
        ]);

        $validated['status'] = 'active';
        $validated['tier'] = 'New';

        $customer = Customer::create($validated);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->formattedName(),
            'phone_number' => $customer->phone_number,
            'tier' => $customer->tier,
            'title' => $customer->title,
        ]);
    }
}
