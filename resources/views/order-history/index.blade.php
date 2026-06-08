<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order History — Restaurant BYOB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f1f5f9 0%, #e9eef5 100%); }

        .order-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: #3b82f6;
        }

        .stat-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-customer { background: #eff6ff; color: #1d4ed8; }
        .badge-cash { background: #f0fdf4; color: #166534; }
        .badge-card { background: #fef3c7; color: #92400e; }
        .badge-items { background: #f3e8ff; color: #6b21a8; }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(3px);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            max-width: 600px;
            width: 92%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0,0,0,0.2);
        }

        .btn-primary   { background: #dc2626; color: #fff; border: none; border-radius: 10px; padding: 10px 16px; font-weight: 700; cursor: pointer; transition: background 0.15s; font-size: 13px; }
        .btn-primary:hover   { background: #b91c1c; }
        .btn-secondary { background: #f1f5f9; color: #374151; border: none; border-radius: 10px; padding: 10px 16px; font-weight: 600; cursor: pointer; transition: background 0.15s; font-size: 13px; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-blue    { background: #2563eb; color: #fff; border: none; border-radius: 10px; padding: 10px 16px; font-weight: 700; cursor: pointer; transition: background 0.15s; font-size: 13px; }
        .btn-blue:hover    { background: #1d4ed8; }

        @media print {
            body > * { display: none !important; }
            #printArea { display: block !important; }
        }
        #printArea { display: none; }

        #billContent { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; }
        #billContent .center  { text-align: center; }
        #billContent .right   { text-align: right; }
        #billContent .bold    { font-weight: bold; }
        #billContent .lg      { font-size: 14px; }
        #billContent .xl      { font-size: 17px; }
        #billContent .sm      { font-size: 10px; }
        #billContent .mt2     { margin-top: 2px; }
        #billContent .mt4     { margin-top: 5px; }
        #billContent .mt8     { margin-top: 10px; }
        #billContent .mb4     { margin-bottom: 5px; }
        #billContent .mb8     { margin-bottom: 10px; }
        #billContent .divider-solid  { border-top: 1px solid #000; margin: 6px 0; }
        #billContent .divider-dashed { border-top: 1px dashed #888; margin: 5px 0; }
        #billContent .divider-double { border-top: 3px double #000; margin: 6px 0; }
        #billContent .row            { display: flex; justify-content: space-between; align-items: flex-start; margin: 2px 0; }
        #billContent .row .label     { flex: 1; }
        #billContent .row .value     { white-space: nowrap; padding-left: 8px; }
    </style>
</head>
<body>

@include('layouts.navbar')
@include('components.sidebar')

<div id="printArea"></div>

<div style="padding-top: 67px; margin-left: 16rem;">
    <div class="w-full px-6 py-8 max-w-screen-2xl mx-auto">

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900" style="letter-spacing:-0.5px;">
                        <i class="fas fa-history mr-3 text-blue-600"></i>Order History
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">View and manage all completed orders</p>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div style="background: #fff; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 12px; font-weight: 600; margin: 0;">Total Completed</p>
                <p style="color: #0f172a; font-size: 24px; font-weight: 800; margin: 8px 0 0;">{{ $orders->total() }}</p>
            </div>
            <div style="background: #fff; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 12px; font-weight: 600; margin: 0;">Total Revenue</p>
                <p style="color: #dc2626; font-size: 24px; font-weight: 800; margin: 8px 0 0;">LKR {{ number_format($orders->sum('total'), 2) }}</p>
            </div>
            <div style="background: #fff; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 12px; font-weight: 600; margin: 0;">Average Order</p>
                <p style="color: #0f172a; font-size: 24px; font-weight: 800; margin: 8px 0 0;">LKR {{ number_format($orders->avg('total') ?? 0, 2) }}</p>
            </div>
            <div style="background: #fff; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 12px; font-weight: 600; margin: 0;">Page</p>
                <p style="color: #0f172a; font-size: 24px; font-weight: 800; margin: 8px 0 0;">{{ $orders->currentPage() }} of {{ $orders->lastPage() }}</p>
            </div>
        </div>

        <!-- Orders List -->
        <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #e2e8f0;">
                <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">
                    <i class="fas fa-receipt mr-2" style="color: #3b82f6;"></i>Orders
                </h2>
            </div>

            <div style="padding: 16px;">
                @if($orders->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($orders as $order)
                            <div class="order-card" onclick="viewOrderDetails({{ $order->id }})">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                    <div>
                                        <p style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">{{ $order->order_number }}</p>
                                        <p style="font-size: 11px; color: #64748b; margin: 4px 0 0;">
                                            <i class="fas fa-calendar-check mr-1"></i>
                                            {{ $order->printed_at ? $order->printed_at->format('M d, Y • h:i A') : 'N/A' }}
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <p style="font-size: 16px; font-weight: 800; color: #dc2626; margin: 0;">LKR {{ number_format($order->total, 2) }}</p>
                                        <button onclick="reprintBill(event, {{ $order->id }})" class="btn-blue" style="margin-top: 6px; padding: 6px 12px; font-size: 11px;">
                                            <i class="fas fa-print mr-1"></i>Reprint
                                        </button>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    @if($order->customer_name)
                                        <span class="stat-badge badge-customer">
                                            <i class="fas fa-user mr-1"></i>{{ $order->customer_name }}
                                        </span>
                                    @endif
                                    <span class="stat-badge badge-items">
                                        <i class="fas fa-box mr-1"></i>{{ $order->items->count() }} items
                                    </span>
                                    <span class="stat-badge {{ $order->payment_method === 'cash' ? 'badge-cash' : 'badge-card' }}">
                                        <i class="fas fa-{{ $order->payment_method === 'cash' ? 'money-bill' : 'credit-card' }} mr-1"></i>
                                        {{ ucwords(str_replace('_', ' ', $order->payment_method ?? 'Cash')) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 48px 16px; color: #94a3b8;">
                        <i class="fas fa-inbox text-4xl mb-3 block"></i>
                        <p style="font-size: 14px; margin: 0;">No completed orders found</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div style="margin-top: 20px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                @if($orders->onFirstPage())
                    <button disabled style="padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #f1f5f9; color: #94a3b8; cursor: not-allowed;">
                        <i class="fas fa-chevron-left mr-1"></i>Previous
                    </button>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; color: #3b82f6; text-decoration: none; cursor: pointer;">
                        <i class="fas fa-chevron-left mr-1"></i>Previous
                    </a>
                @endif

                @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                    @if($page == $orders->currentPage())
                        <button style="padding: 8px 12px; border-radius: 6px; border: 1px solid #3b82f6; background: #3b82f6; color: #fff; font-weight: 600;">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; color: #374151; text-decoration: none;">{{ $page }}</a>
                    @endif
                @endforeach

                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; color: #3b82f6; text-decoration: none; cursor: pointer;">
                        Next<i class="fas fa-chevron-right ml-1"></i>
                    </a>
                @else
                    <button disabled style="padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #f1f5f9; color: #94a3b8; cursor: not-allowed;">
                        Next<i class="fas fa-chevron-right ml-1"></i>
                    </button>
                @endif
            </div>
        @endif

    </div>
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="modal-overlay">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">
                <i class="fas fa-receipt mr-2" style="color: #3b82f6;"></i>Order Details
            </h2>
            <button onclick="closeModal('orderDetailsModal')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #94a3b8; line-height: 1;">&times;</button>
        </div>

        <!-- Order Info -->
        <div style="background: #f8fafc; border-radius: 10px; padding: 12px; margin-bottom: 16px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12px;">
                <div>
                    <p style="color: #64748b; margin: 0 0 3px; font-weight: 600;">Order #</p>
                    <p id="modalOrderNumber" style="color: #0f172a; margin: 0; font-weight: 700;">—</p>
                </div>
                <div>
                    <p style="color: #64748b; margin: 0 0 3px; font-weight: 600;">Type</p>
                    <p id="modalOrderType" style="color: #0f172a; margin: 0; font-weight: 700;">—</p>
                </div>
                <div>
                    <p style="color: #64748b; margin: 0 0 3px; font-weight: 600;">Customer</p>
                    <p id="modalCustomer" style="color: #0f172a; margin: 0; font-weight: 700;">—</p>
                </div>
                <div>
                    <p style="color: #64748b; margin: 0 0 3px; font-weight: 600;">Payment</p>
                    <p id="modalPayment" style="color: #0f172a; margin: 0; font-weight: 700; text-transform: capitalize;">—</p>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div style="margin-bottom: 16px;">
            <h3 style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 10px; letter-spacing: 0.05em;">Menu Items</h3>
            <div id="modalItems" style="background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; display: flex; flex-direction: column; gap: 8px; padding: 12px;"></div>
        </div>

        <!-- Totals -->
        <div style="background: #f8fafc; border-radius: 10px; padding: 12px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 6px;">
                <span style="color: #64748b;">Subtotal</span>
                <span id="modalSubtotal" style="font-weight: 600; color: #374151;">LKR 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 6px;">
                <span style="color: #64748b;">Discount</span>
                <span id="modalDiscount" style="font-weight: 600; color: #ef4444;">LKR 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; padding-top: 6px; border-top: 2px solid #e2e8f0;">
                <span style="color: #0f172a;">Total</span>
                <span id="modalTotal" style="color: #dc2626;">LKR 0.00</span>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 10px;">
            <button onclick="closeModal('orderDetailsModal')" class="btn-secondary" style="flex: 1;">Close</button>
            <button onclick="reprintFromModal()" class="btn-blue" style="flex: 1;">
                <i class="fas fa-print mr-1"></i>Reprint Bill
            </button>
        </div>
    </div>
</div>

<!-- Print Bill Modal -->
<div id="printBillModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 450px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Bill Preview</h2>
            <button onclick="closeModal('printBillModal')" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #94a3b8; line-height: 1;">&times;</button>
        </div>
        <div id="billContent" style="background: #fafafa; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0; margin-bottom: 16px;"></div>
        <div style="display: flex; gap: 10px;">
            <button onclick="closeModal('printBillModal')" class="btn-secondary" style="flex: 1;">Close</button>
            <button onclick="printBillContent()" class="btn-primary" style="flex: 1;">
                <i class="fas fa-print mr-1"></i>Print
            </button>
        </div>
    </div>
</div>

<script>
    let currentOrder = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#39;');
    }

    async function viewOrderDetails(orderId) {
        try {
            const res = await fetch('{{ route("pos.order.show", ":id") }}'.replace(':id', orderId));
            if (!res.ok) {
                alert('Failed to load order details');
                return;
            }
            const order = await res.json();
            currentOrder = order;
            renderOrderDetails(order);
            openModal('orderDetailsModal');
        } catch (e) {
            console.error('Error:', e);
            alert('Error loading order');
        }
    }

    function renderOrderDetails(order) {
        document.getElementById('modalOrderNumber').textContent = order.order_number;
        document.getElementById('modalOrderType').textContent = (order.order_type || 'dine_in').charAt(0).toUpperCase() + (order.order_type || 'dine_in').slice(1).replace('_', ' ');
        document.getElementById('modalCustomer').textContent = order.customer_name || 'Walk-in';
        document.getElementById('modalPayment').textContent = (order.payment_method || 'cash').replace('_', ' ');

        // Items
        const itemsHtml = (order.items || []).map(item => {
            return '<div style="padding: 8px 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: start;">'
                + '<div style="flex: 1;">'
                + '<p style="font-size: 12px; font-weight: 700; color: #0f172a; margin: 0;">' + escapeHtml(item.product_name) + '</p>'
                + '<p style="font-size: 10px; color: #94a3b8; margin: 2px 0 0;">' + item.quantity + ' × LKR ' + item.unit_price.toFixed(2) + '</p>'
                + (item.kitchen_notes ? '<p style="font-size: 10px; color: #f59e0b; margin: 2px 0 0;"><i class="fas fa-note-sticky mr-1"></i>' + escapeHtml(item.kitchen_notes) + '</p>' : '')
                + '</div>'
                + '<span style="font-size: 12px; font-weight: 700; color: #0f172a; min-width: 70px; text-align: right;">LKR ' + item.subtotal.toFixed(2) + '</span>'
                + '</div>';
        }).join('');
        document.getElementById('modalItems').innerHTML = itemsHtml || '<p style="text-align: center; color: #94a3b8; padding: 16px;">No items</p>';

        // Totals
        document.getElementById('modalSubtotal').textContent = 'LKR ' + order.subtotal.toFixed(2);
        document.getElementById('modalDiscount').textContent = 'LKR ' + order.discount_amount.toFixed(2);
        document.getElementById('modalTotal').textContent = 'LKR ' + order.total.toFixed(2);
    }

    async function reprintBill(event, orderId) {
        event.stopPropagation();
        await generateBill(orderId);
    }

    async function reprintFromModal() {
        if (!currentOrder) return;
        await generateBill(currentOrder.id);
    }

    async function generateBill(orderId) {
        try {
            const res = await fetch('{{ route("pos.order.bill.reprint", ":id") }}'.replace(':id', orderId));
            if (!res.ok) {
                alert('Failed to load bill');
                return;
            }
            const billData = await res.json();
            if (!billData.success) {
                alert(billData.message || 'Failed to generate bill');
                return;
            }
            renderBill(billData);
            closeModal('orderDetailsModal');
            openModal('printBillModal');
        } catch (e) {
            console.error('Error:', e);
            alert('Error generating bill');
        }
    }

    function renderBill(billData) {
        const header = '<div class="center mb8">'
            + '<div class="bold xl">JBL FOOD CORNER</div>'
            + '<div class="sm mt4">Your favourite dining destination</div>'
            + '<div class="divider-double mt8"></div>'
            + '<div class="bold lg mt4">RECEIPT</div>'
            + '</div>';

        const meta = '<div class="row sm mt4"><span class="label">Order #</span><span class="value">' + escapeHtml(billData.order_number) + '</span></div>'
            + '<div class="row sm"><span class="label">Token #</span><span class="value">' + escapeHtml(billData.token_number) + '</span></div>'
            + '<div class="row sm"><span class="label">Date</span><span class="value">' + (billData.printed_at ? new Date(billData.printed_at).toLocaleDateString() : '—') + '</span></div>';

        const customerLine = billData.customer_name ? '<div class="row sm"><span class="label">Customer</span><span class="value">' + escapeHtml(billData.customer_name) + '</span></div>' : '';

        const items = (billData.items || []).map(item => {
            return '<div style="margin: 3px 0;">'
                + '<div style="display: flex;">'
                + '<span style="flex: 1; font-weight: bold;">' + escapeHtml(item.product_name) + '</span>'
                + '<span style="width: 30px; text-align: center;">' + item.quantity + '</span>'
                + '<span style="width: 70px; text-align: right;">LKR' + item.subtotal.toFixed(2) + '</span>'
                + '</div>'
                + '<div class="sm">' + item.quantity + ' x LKR' + item.unit_price.toFixed(2) + '</div>'
                + '</div>';
        }).join('');

        const itemHeader = '<div class="divider-solid"></div>'
            + '<div style="display: flex; font-size: 9px; font-weight: bold; margin: 2px 0;">'
            + '<span style="flex: 1;">ITEM</span>'
            + '<span style="width: 30px; text-align: center;">QTY</span>'
            + '<span style="width: 70px; text-align: right;">AMOUNT</span>'
            + '</div>'
            + '<div class="divider-dashed"></div>';

        const totals = '<div class="divider-solid"></div>'
            + '<div class="row mt4"><span class="label">Subtotal</span><span class="value">LKR' + billData.subtotal.toFixed(2) + '</span></div>'
            + (billData.discount_amount > 0 ? '<div class="row"><span class="label">Discount</span><span class="value">-LKR' + billData.discount_amount.toFixed(2) + '</span></div>' : '')
            + '<div class="divider-double"></div>'
            + '<div class="row bold lg"><span class="label">TOTAL</span><span class="value">LKR' + billData.total.toFixed(2) + '</span></div>'
            + '<div class="divider-double"></div>'
            + '<div class="center sm mt8"><p style="margin: 0;">Thank you for your order!</p>'
            + '<p style="margin: 4px 0 0;">Contact: +94 702 398 400</p></div>';

        document.getElementById('billContent').innerHTML = header + meta + customerLine + itemHeader + items + totals;
    }

    function printBillContent() {
        window.print();
    }

    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    // Close modal on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal(overlay.id);
        });
    });
</script>

</body>
</html>
