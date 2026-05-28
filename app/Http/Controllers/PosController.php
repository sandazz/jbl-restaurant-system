<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TierDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $tables       = RestaurantTable::all()->load('activeOrder.items');
        $categories   = Category::where('status', 'active')->orderBy('sort_order')->get();
        $products     = Product::where('status', 'active')->get();
        $modules      = auth()->user()->role->modules()->get();
        $tierDiscounts = TierDiscount::activeMap(); // ['VIP' => 15.0, 'Moderate' => 10.0, ...]

        return view('modules.pos', [
            'tables'        => $tables,
            'categories'    => $categories,
            'products'      => $products,
            'modules'       => $modules,
            'tierDiscounts' => $tierDiscounts,
        ]);
    }

    public function getTables()
    {
        $tables = RestaurantTable::with('activeOrder.items')->get()->map(function ($table) {
            $activeOrder = $table->activeOrder;
            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'status' => $table->status,
                'section' => $table->section,
                'occupied_at' => $table->occupied_at,
                'has_order' => $activeOrder ? true : false,
                'order_id' => $activeOrder?->id,
                'order_items_count' => $activeOrder?->items->count() ?? 0,
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
        $validated = $request->validate([
            'table_id' => 'nullable|exists:restaurant_tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'order_type' => 'required|in:dine_in,takeaway,delivery,vip_room',
            'waiter_name' => 'nullable|string',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-' . Str::random(8),
            'table_id' => $validated['table_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'user_id' => auth()->id(),
            'order_type' => $validated['order_type'],
            'waiter_name' => $validated['waiter_name'] ?? auth()->user()->name,
        ]);

        if (!empty($validated['table_id'])) {
            RestaurantTable::find($validated['table_id'])->update([
                'status' => 'occupied',
                'occupied_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    public function getOrder(Order $order)
    {
        $order->load('items.product', 'table', 'customer');

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
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

        $product = Product::find($validated['product_id']);
        $price = $product->selling_price ?? $product->price;

        $existingItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $validated['quantity'];
            $existingItem->subtotal = $existingItem->unit_price * $existingItem->quantity;
            $existingItem->save();
            $item = $existingItem;
        } else {
            $item = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $price,
                'quantity' => $validated['quantity'],
                'subtotal' => $price * $validated['quantity'],
                'kitchen_notes' => $validated['kitchen_notes'] ?? null,
                'is_bar_item' => $validated['is_bar_item'] ?? false,
            ]);
        }

        $this->updateOrderTotals($order);

        return response()->json([
            'success' => true,
            'item_id' => $item->id,
            'message' => $product->name . ' added to order',
        ]);
    }

    public function removeItem(Request $request, Order $order, OrderItem $item)
    {
        $item->delete();
        $this->updateOrderTotals($order);

        return response()->json(['success' => true, 'message' => 'Item removed']);
    }

    public function updateItem(Request $request, Order $order, OrderItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'kitchen_notes' => 'nullable|string',
        ]);

        $item->update([
            'quantity' => $validated['quantity'],
            'subtotal' => $item->unit_price * $validated['quantity'],
            'kitchen_notes' => $validated['kitchen_notes'] ?? $item->kitchen_notes,
        ]);

        $this->updateOrderTotals($order);

        return response()->json(['success' => true, 'message' => 'Item updated']);
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
        $order->update(['kot_printed_at' => now()]);
        $kitchenItems = $order->items->where('is_bar_item', false)->values();

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'items' => $kitchenItems->map(fn($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'kitchen_notes' => $item->kitchen_notes,
            ]),
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
            'table_number' => $order->table?->table_number,
            'items_count' => $order->items->count(),
            'total' => (float) $order->total,
        ]));
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

    public function printWaiterBill(Order $order)
    {
        $order->load('items', 'table');
        $order->update(['waiter_bill_printed_at' => now()]);

        $subtotal = $order->items->sum('subtotal');
        $total = $subtotal;

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'table_number' => $order->table?->table_number,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'subtotal' => (float) $subtotal,
            'tax_amount' => 0,
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
            RestaurantTable::find($order->table_id)->update([
                'status' => 'available',
                'occupied_at' => null,
            ]);
        }
        return response()->json(['success' => true, 'message' => 'Table closed']);
    }

    public function payOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,bank_transfer,mixed',
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

        // Only update table status if this order is for dine-in (has a table_id)
        if ($order->table_id) {
            $table = RestaurantTable::find($order->table_id);
            if ($table) {
                $table->update([
                    'status'      => 'available',
                    'occupied_at' => null,
                ]);
            }
        }

        return response()->json([
            'success'         => true,
            'order_number'    => $order->order_number,
            'table_number'    => $order->table?->table_number,
            'table_name'      => $order->table?->name,
            'customer_name'   => $order->customer_name,
            'customer_phone'  => $order->customer_phone,
            'subtotal'        => (float) $subtotal,
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
}
