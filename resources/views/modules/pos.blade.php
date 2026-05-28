<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS & Billing — Restaurant BYOB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; height: 100vh; overflow: hidden; display: flex; flex-direction: column; }

        /* ── Layout ── */
        .pos-grid { display: grid; grid-template-columns: 320px 1fr 520px; flex: 1; min-height: 0; }

        /* ── Panels ── */
        .tables-panel  { background: #fff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }
        .menu-panel    { background: #f8fafc; display: flex; flex-direction: column; overflow: hidden; }
        .bill-panel    { background: #fff; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }

        /* ── Table cards ── */
        .table-card {
            cursor: pointer;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            transition: all 0.18s ease;
            position: relative;
            background: #fff;
            user-select: none;
        }
        .table-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .table-card.selected { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }

        /* Status colours */
        .table-card.available { border-color: #22c55e; background: linear-gradient(135deg,#f0fdf4,#dcfce7); }
        .table-card.available:hover { border-color: #16a34a; }
        .table-card.occupied  { border-color: #ef4444; background: linear-gradient(135deg,#fff1f1,#fee2e2); }
        .table-card.occupied:hover  { border-color: #dc2626; }
        .table-card.reserved  { border-color: #f59e0b; background: linear-gradient(135deg,#fffbeb,#fef3c7); }
        .table-card.reserved:hover  { border-color: #d97706; }
        .table-card.cleaning  { border-color: #94a3b8; background: #f8fafc; }

        /* Status badge on card */
        .table-status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 4px; }
        .dot-available { background: #22c55e; }
        .dot-occupied  { background: #ef4444; }
        .dot-reserved  { background: #f59e0b; }
        .dot-cleaning  { background: #94a3b8; }

        /* Bottom action bar that expands on click */
        .table-card-actions {
            display: none;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed rgba(0,0,0,0.12);
            gap: 6px;
        }
        .table-card.expanded .table-card-actions { display: flex; }

        /* ── Category pills ── */
        .cat-pill {
            padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
            border: 2px solid #e2e8f0; cursor: pointer; white-space: nowrap; transition: all 0.15s;
            background: #fff; color: #64748b;
        }
        .cat-pill:hover { border-color: #dc2626; color: #dc2626; }
        .cat-pill.active { background: #dc2626; color: #fff; border-color: #dc2626; }

        /* ── Product cards ── */
        .product-card {
            background: #fff; border: 2px solid #e2e8f0; border-radius: 12px;
            padding: 14px 10px; cursor: pointer; transition: all 0.18s; text-align: center;
        }
        .product-card:hover { border-color: #dc2626; box-shadow: 0 4px 16px rgba(220,38,38,0.15); transform: translateY(-2px); }
        .product-card:active { transform: scale(0.97); }

        /* ── Bill items ── */
        .bill-item {
            display: flex; align-items: center; padding: 10px 0;
            border-bottom: 1px solid #f1f5f9; gap: 8px;
        }
        .qty-btn {
            width: 26px; height: 26px; border: 1.5px solid #e2e8f0; border-radius: 6px;
            background: #f8fafc; cursor: pointer; font-size: 13px; font-weight: bold;
            display: flex; align-items: center; justify-content: center; transition: all 0.12s;
            color: #374151;
        }
        .qty-btn:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

        /* ── Payment method buttons ── */
        .pay-method-btn {
            flex: 1; padding: 10px 4px; border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 12px; font-weight: 700; cursor: pointer; text-align: center;
            background: #fff; transition: all 0.15s; color: #64748b;
        }
        .pay-method-btn:hover { border-color: #3b82f6; color: #3b82f6; }
        .pay-method-btn.active { border-color: #dc2626; background: #fef2f2; color: #dc2626; }

        /* ── Modals ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,0.55); backdrop-filter: blur(3px);
            z-index: 50; align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #fff; border-radius: 16px; padding: 28px;
            max-width: 480px; width: 92%; max-height: 92vh; overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0,0,0,0.2);
        }

        /* ── Active order banner ── */
        #activeOrderBanner {
            align-items: center;
            justify-content: space-between;
        }
        #activeOrderBanner[style*="display:flex"] {
            display: flex !important;
        }

        /* ── Live bill prompt ── */
        .live-bill-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);
            z-index: 60; align-items: center; justify-content: center;
        }
        .live-bill-overlay.open { display: flex; }

        /* ── Buttons ── */
        .btn-primary   { background: #dc2626; color: #fff; border: none; border-radius: 10px; padding: 10px 16px; font-weight: 700; cursor: pointer; transition: background 0.15s; font-size: 13px; }
        .btn-primary:hover   { background: #b91c1c; }
        .btn-secondary { background: #f1f5f9; color: #374151; border: none; border-radius: 10px; padding: 10px 16px; font-weight: 600; cursor: pointer; transition: background 0.15s; font-size: 13px; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-green   { background: #16a34a; color: #fff; border: none; border-radius: 10px; padding: 10px 16px; font-weight: 700; cursor: pointer; transition: background 0.15s; font-size: 13px; }
        .btn-green:hover   { background: #15803d; }
        .btn-blue    { background: #2563eb; color: #fff; border: none; border-radius: 10px; padding: 10px 16px; font-weight: 700; cursor: pointer; transition: background 0.15s; font-size: 13px; }
        .btn-blue:hover    { background: #1d4ed8; }
        .btn-orange  { background: #ea580c; color: #fff; border: none; border-radius: 10px; padding: 10px 14px; font-weight: 700; cursor: pointer; transition: background 0.15s; font-size: 12px; }
        .btn-orange:hover  { background: #c2410c; }
        .btn-purple  { background: #7c3aed; color: #fff; border: none; border-radius: 10px; padding: 10px 14px; font-weight: 700; cursor: pointer; transition: background 0.15s; font-size: 12px; }
        .btn-purple:hover  { background: #6d28d9; }

        /* ── Scrollbars ── */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        /* ── Notification toast ── */
        #toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 100;
            background: #1e293b; color: #fff; padding: 12px 20px; border-radius: 10px;
            font-size: 13px; font-weight: 500; opacity: 0; transition: opacity 0.3s;
            pointer-events: none; max-width: 300px;
        }
        #toast.show { opacity: 1; }
        #toast.success { background: #166534; }
        #toast.error   { background: #991b1b; }

        /* ── Print ── */
        @media print {
            body > * { display: none !important; }
            #printArea { display: block !important; }
        }
        #printArea { display: none; }
    </style>
</head>
<body>

@include('layouts.navbar')

<!-- Hidden print area -->
<div id="printArea"></div>

<div class="pos-grid" style="margin-top: 64px; height: calc(100vh - 64px);">

    <!-- ════════════════════════════════════════
         COLUMN 1 — TABLES PANEL
    ════════════════════════════════════════ -->
    <div class="tables-panel">

        <!-- Header -->
        <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; flex-shrink: 0;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <h2 style="font-size:16px; font-weight:800; color:#0f172a; margin:0;">
                    <i class="fas fa-chair" style="color:#dc2626; margin-right:6px;"></i>Tables
                </h2>
                <span id="tableStatusBadge" style="font-size:11px; color:#64748b; font-weight:600;"></span>
            </div>
            <!-- Legend -->
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
                <span style="font-size:10px; font-weight:600; color:#16a34a;"><span class="table-status-dot dot-available"></span>Free</span>
                <span style="font-size:10px; font-weight:600; color:#dc2626;"><span class="table-status-dot dot-occupied"></span>Occupied</span>
                <span style="font-size:10px; font-weight:600; color:#d97706;"><span class="table-status-dot dot-reserved"></span>Reserved</span>
            </div>
            <!-- Filter tabs -->
            <div style="display:flex; gap:6px; margin-bottom:10px;">
                <button onclick="filterTables('all', this)" class="cat-pill active" style="padding:4px 12px;">All</button>
                <button onclick="filterTables('main', this)" class="cat-pill" style="padding:4px 12px;">Main</button>
                <button onclick="filterTables('vip', this)" class="cat-pill" style="padding:4px 12px;">VIP</button>
            </div>
            <!-- Takeaway Order Button -->
            <button onclick="startTakeawayOrder()" class="btn-primary" style="width:100%; padding:10px; font-size:12px; font-weight:700;">
                <i class="fas fa-shopping-bag" style="margin-right:6px;"></i>Takeaway Order
            </button>
        </div>

        <!-- Tables list -->
        <div style="flex:1; overflow-y:auto; padding:12px; display:grid; grid-template-columns: repeat(2, 1fr); gap: 10px; align-content: start;" id="tablesContainer">
            <p style="grid-column:1/-1; text-align:center; color:#94a3b8; padding:32px 0; font-size:13px;">Loading tables…</p>
        </div>

    </div>

    <!-- ════════════════════════════════════════
         COLUMN 2 — MENU PANEL
    ════════════════════════════════════════ -->
    <div class="menu-panel">

        <!-- Toolbar -->
        <div style="padding:16px; background:#fff; border-bottom:1px solid #e2e8f0; flex-shrink:0;">
            <div style="display:flex; gap:10px; margin-bottom:12px; align-items:center;">
                <div style="flex:1; position:relative;">
                    <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
                    <input type="text" id="searchInput" placeholder="Search products or scan barcode…"
                           style="width:100%; padding:9px 12px 9px 36px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:13px; outline:none; background:#f8fafc;"
                           onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                <select id="orderTypeSelect"
                        style="padding:9px 12px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:13px; background:#f8fafc; color:#374151; outline:none; cursor:pointer;">
                    <option value="dine_in">Dine In</option>
                    <option value="takeaway">Takeaway</option>
                    <option value="delivery">Delivery</option>
                    <option value="vip_room">VIP Room</option>
                </select>
            </div>
            <!-- Categories -->
            <div style="display:flex; gap:8px; overflow-x:auto; padding-bottom:2px;" id="categoriesContainer">
                <button class="cat-pill active" data-category="0" onclick="selectCategory(0, this)">All</button>
            </div>
        </div>

        <!-- Active order indicator -->
        <div id="activeOrderBanner" style="display:none; background:linear-gradient(90deg,#fef2f2,#fff1f1); border-bottom:1px solid #fecaca; padding:8px 16px; flex-shrink:0;">
            <span style="font-size:12px; font-weight:600; color:#dc2626; flex:1;">
                <i class="fas fa-circle-dot" style="margin-right:4px;"></i>
                <span id="activeOrderText">Adding to Table —</span>
                <span style="color:#374151; font-weight:500; margin-left:4px;">tap a product to add it</span>
            </span>
            <button id="closeOrderBtn" onclick="closeCurrentOrder(); event.stopPropagation();" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:18px; padding:0 8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center; transition:all 0.2s; flex-shrink:0;">
                <i class="fas fa-times-circle" style="font-size:18px;"></i>
            </button>
        </div>

        <!-- Products grid -->
        <div style="flex:1; overflow-y:auto; padding:16px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:12px;" id="productsContainer">
                <p style="grid-column:1/-1; text-align:center; color:#94a3b8; padding:48px 0; font-size:13px;">Loading products…</p>
            </div>
        </div>

    </div>

    <!-- ════════════════════════════════════════
         COLUMN 3 — BILL PANEL
    ════════════════════════════════════════ -->
    <div class="bill-panel">

        <!-- Zone 1: Header with Table Info -->
        <div style="padding:12px 16px; border-bottom:1px solid #e2e8f0; flex-shrink:0; background:#fff;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <h3 style="font-size:14px; font-weight:800; color:#0f172a; margin:0;">
                    <i class="fas fa-receipt" style="color:#dc2626; margin-right:6px;"></i>Order
                </h3>
                <button onclick="loadHeldOrders()" style="font-size:10px; background:#fef3c7; color:#92400e; border:none; padding:4px 8px; border-radius:6px; cursor:pointer; font-weight:700;">
                    <i class="fas fa-pause-circle" style="margin-right:2px;"></i>Held <span id="heldCount" style="background:#f59e0b;color:#fff;border-radius:8px;padding:0px 5px; font-size:9px;">0</span>
                </button>
            </div>
            <div id="selectedTableLabel" style="font-size:12px; font-weight:700; color:#64748b;">
                <i class="fas fa-arrow-left" style="font-size:10px; margin-right:4px;"></i>Select a table to begin
            </div>
        </div>

        <!-- Zone 2: Expandable Customer Info -->
        <div style="padding:0; border-bottom:1px solid #e2e8f0; flex-shrink:0; background:#f8fafc;">
            <button id="customerInfoToggle" onclick="toggleCustomerInfo()" style="width:100%; padding:10px 16px; background:none; border:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; text-align:left;">
                <div style="display:flex; align-items:center;">
                    <i class="fas fa-user-circle" style="color:#1d4ed8; margin-right:6px; font-size:13px;"></i>
                    <span style="font-size:11px; font-weight:700; color:#1d4ed8; text-transform:uppercase;">Customer</span>
                </div>
                <i class="fas fa-chevron-down" id="customerInfoChevron" style="font-size:11px; color:#64748b;"></i>
            </button>
            <div id="customerInfoSection" style="display:none; padding:8px 16px; border-top:1px solid #e2e8f0;">
                <!-- Selected customer chip (shown after picking from list) -->
                <div id="selectedCustomerChip" style="display:none; align-items:center; justify-content:space-between; background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:6px; padding:5px 8px; margin-bottom:6px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-user-check" style="color:#1d4ed8; font-size:11px;"></i>
                        <div>
                            <div id="chipName" style="font-size:11px; font-weight:600; color:#1e3a8a;"></div>
                            <div id="chipPhone" style="font-size:10px; color:#64748b;"></div>
                        </div>
                        <span id="chipTier" style="font-size:9px; font-weight:700; padding:1px 6px; border-radius:99px; border:1px solid;"></span>
                    </div>
                    <button onclick="clearSelectedCustomer()" style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:2px 4px;" title="Clear">
                        <i class="fas fa-times" style="font-size:11px;"></i>
                    </button>
                </div>

                <!-- Search inputs (hidden when a customer is selected) -->
                <div id="customerSearchInputs" style="display:grid; grid-template-columns:1fr 1fr; gap:6px; position:relative;">
                    <div style="position:relative;">
                        <input type="text" id="customerName" placeholder="Search name…"
                               autocomplete="off"
                               style="font-size:11px; border:1.5px solid #bfdbfe; border-radius:6px; padding:6px 8px; background:#fff; outline:none; width:100%; box-sizing:border-box;"
                               oninput="onCustomerInput('name')"
                               onfocus="this.style.borderColor='#3b82f6'"
                               onblur="this.style.borderColor='#bfdbfe'">
                    </div>
                    <div style="position:relative;">
                        <input type="text" id="customerPhone" placeholder="Search phone…"
                               autocomplete="off"
                               style="font-size:11px; border:1.5px solid #bfdbfe; border-radius:6px; padding:6px 8px; background:#fff; outline:none; width:100%; box-sizing:border-box;"
                               oninput="onCustomerInput('phone')"
                               onfocus="this.style.borderColor='#3b82f6'"
                               onblur="this.style.borderColor='#bfdbfe'">
                    </div>
                </div>

                <!-- Dropdown results (absolutely positioned below the inputs) -->
                <div id="customerDropdown" style="display:none; position:absolute; z-index:999; background:#fff; border:1.5px solid #bfdbfe; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); overflow:hidden; margin-top:2px; width:calc(100% - 32px);"></div>
            </div>
        </div>

        <!-- Zone 3: Order items (scrollable) - MAIN AREA -->
        <div style="flex:1; overflow-y:auto; padding:12px 16px; background:#fafafa;" id="billItemsWrapper">
            <div id="billItems">
                <div style="text-align:center; padding:48px 0; color:#cbd5e1;">
                    <i class="fas fa-utensils" style="font-size:36px; margin-bottom:12px; display:block;"></i>
                    <p style="font-size:12px; margin:0;">Select a table, then add items</p>
                </div>
            </div>
        </div>

        <!-- Zone 4: Fixed bottom controls -->
        <div style="border-top:1px solid #e2e8f0; padding:12px 16px; background:#fff; flex-shrink:0; display:flex; flex-direction:column; gap:8px;">

            <!-- Totals -->
            <div style="font-size:12px; display:flex; flex-direction:column; gap:4px;">
                <div style="display:flex; justify-content:space-between; color:#64748b; font-size:11px;">
                    <span>Subtotal</span>
                    <span id="subtotalDisplay" style="font-weight:600; color:#374151;">Rs. 0.00</span>
                </div>
                {{-- Tier discount indicator --}}
                <div id="tierDiscountBadge" style="display:none; align-items:center; gap:5px; font-size:10px; font-weight:600; color:#1d4ed8; background:#eff6ff; border:1px solid #bfdbfe; border-radius:5px; padding:3px 8px;">
                    <i class="fas fa-tag" style="font-size:9px;"></i>
                    <span></span>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px;">
                    <span style="color:#64748b;">Discount</span>
                    <div style="display:flex; gap:4px;">
                        <select id="discountType" onchange="recalcTotal()"
                                style="font-size:10px; border:1px solid #e2e8f0; border-radius:5px; padding:3px 6px; background:#f8fafc; outline:none; cursor:pointer;">
                            <option value="">None</option>
                            <option value="percentage">%</option>
                            <option value="fixed">Rs</option>
                        </select>
                        <input type="number" id="discountValue" placeholder="0" min="0" oninput="recalcTotal()"
                               style="width:50px; font-size:10px; border:1px solid #e2e8f0; border-radius:5px; padding:3px 6px; outline:none; background:#f8fafc;">
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; padding-top:4px; border-top:2px solid #f1f5f9; font-weight:700; font-size:14px; color:#dc2626;">
                    <span>Total</span>
                    <span id="totalDisplay">Rs. 0.00</span>
                </div>
            </div>

            <!-- Payment method (hidden until items exist) -->
            <div id="paymentSection" style="display:none; padding:8px 0; border-top:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
                <div style="font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:#64748b; margin-bottom:6px;">Method</div>
                <div style="display:flex; gap:5px; margin-bottom:8px;">
                    <button class="pay-method-btn active" data-method="cash" onclick="selectPaymentMethod('cash')" style="flex:1; padding:6px 4px; font-size:10px;">
                        <i class="fas fa-money-bill-wave" style="display:block; font-size:13px; margin-bottom:2px;"></i>Cash
                    </button>
                    <button class="pay-method-btn" data-method="card" onclick="selectPaymentMethod('card')" style="flex:1; padding:6px 4px; font-size:10px;">
                        <i class="fas fa-credit-card" style="display:block; font-size:13px; margin-bottom:2px;"></i>Card
                    </button>
                    <button class="pay-method-btn" data-method="bank_transfer" onclick="selectPaymentMethod('bank_transfer')" style="flex:1; padding:6px 4px; font-size:10px;">
                        <i class="fas fa-university" style="display:block; font-size:13px; margin-bottom:2px;"></i>Bank
                    </button>
                </div>
                <!-- Cash amount input -->
                <div id="cashSection" style="display:flex; gap:6px;">
                    <div style="flex:1;">
                        <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Paid</label>
                        <input type="number" id="amountPaid" placeholder="0.00" min="0" oninput="updateChange()"
                               style="width:100%; font-size:11px; font-weight:700; border:1px solid #e2e8f0; border-radius:5px; padding:5px 6px; outline:none;"
                               onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div style="flex:1;">
                        <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Change</label>
                        <div id="changeDisplay" style="font-size:12px; font-weight:700; color:#16a34a; padding:5px 6px; background:#f0fdf4; border-radius:5px; border:1px solid #bbf7d0; text-align:center;">Rs. 0.00</div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div id="orderControls" style="display:flex; flex-direction:column; gap:6px;">

                <!-- Row 1: KOT -->
                <div style="display:flex; gap:6px;">
                    <button onclick="printKot()" class="btn-orange" style="flex:1; padding:8px 6px; font-size:11px;">
                        <i class="fas fa-receipt" style="margin-right:3px;"></i>KOT
                    </button>
                </div>

                <!-- Row 2: Waiter Bill + Pay (side by side) -->
                <div id="waiterPayRow" style="display:none; gap:6px; display:flex;">
                    <button onclick="printBill()" id="waiterBillBtn" class="btn-blue" style="flex:1; padding:8px 6px; font-size:11px;">
                        <i class="fas fa-file-invoice" style="margin-right:3px;"></i>Bill
                    </button>
                    <button onclick="initiatePayment()" id="payBtn" class="btn-green" style="flex:1; padding:8px 6px; font-size:11px;">
                        <i class="fas fa-check-circle" style="margin-right:3px;"></i>Pay
                    </button>
                </div>

                <!-- Row 3: Hold -->
                <button onclick="holdCurrentOrder()" id="holdBtn" class="btn-secondary" style="display:none; width:100%; padding:8px; font-size:11px;">
                    <i class="fas fa-pause" style="margin-right:3px;"></i>Hold Order
                </button>

            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL: Final Bill (paid)
══════════════════════════════════════════════════ -->
<div id="finalBillModal" class="modal-overlay">
    <div class="modal-box" style="max-width:380px; padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="font-size:16px; font-weight:800; color:#0f172a; margin:0;"><i class="fas fa-receipt" style="color:#16a34a; margin-right:6px;"></i>Final Bill</h2>
            <button onclick="closeModal('finalBillModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8; line-height:1;">&times;</button>
        </div>
        <div id="billContent" style="font-family:'Courier New',monospace; background:#fafafa; border-radius:8px; padding:16px; font-size:12px; border:1px solid #e2e8f0;"></div>
        <div style="display:flex; gap:10px; margin-top:16px;">
            <button onclick="closeModal('finalBillModal')" class="btn-secondary" style="flex:1;">Close</button>
            <button onclick="printBillContent()" class="btn-primary" style="flex:1;"><i class="fas fa-print" style="margin-right:4px;"></i>Print</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL: KOT
══════════════════════════════════════════════════ -->
<div id="kotModal" class="modal-overlay">
    <div class="modal-box" style="max-width:400px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;"><i class="fas fa-utensils" style="color:#ea580c; margin-right:6px;"></i>Kitchen Order</h2>
            <button onclick="closeModal('kotModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <div style="background:#f8fafc; border-radius:10px; padding:12px; margin-bottom:16px;">
            <p style="font-size:13px; font-weight:700; margin:0 0 3px;" id="kotOrderNumber">Order #—</p>
            <p style="font-size:13px; color:#64748b; margin:0;" id="kotTableNumber">Table —</p>
        </div>
        <div id="kotItems" style="max-height:260px; overflow-y:auto; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; padding:12px; display:flex; flex-direction:column; gap:10px;"></div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button onclick="closeModal('kotModal')" class="btn-secondary" style="flex:1;">Close</button>
            <button onclick="printKotContent()" class="btn-orange" style="flex:1;"><i class="fas fa-print" style="margin-right:4px;"></i>Print</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL: Held Orders
══════════════════════════════════════════════════ -->
<div id="heldOrdersModal" class="modal-overlay">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;"><i class="fas fa-pause-circle" style="color:#f59e0b; margin-right:6px;"></i>Held Orders</h2>
            <button onclick="closeModal('heldOrdersModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <div id="heldOrdersList" style="display:flex; flex-direction:column; gap:10px; max-height:400px; overflow-y:auto;"></div>
    </div>
</div>

<!-- Toast notification -->
<div id="toast"></div>

<script>
    // ── State ──
    let currentOrder  = null;
    let currentTable  = null;
    let allTables     = [];
    let allProducts   = [];
    let allCategories = @json($categories);
    let selectedPaymentMethod = 'cash';
    let currentKotContent     = '';
    let currentBillContent    = '';
    let tableFilter           = 'all';

    // ── Bootstrap ──
    async function initPos() {
        await loadTables();
        loadCategories();
        await loadProducts();
        loadHeldOrders();
        setupEventListeners();
    }

    // ═══════════════════════════════════════════
    // TABLES
    // ═══════════════════════════════════════════

    async function loadTables() {
        try {
            const res = await fetch('{{ route("pos.tables") }}');
            if (!res.ok) { toast('Failed to load tables', 'error'); return; }
            allTables = await res.json();
            renderTables();
            updateTableStatusBadge();
        } catch (e) {
            console.error('Load tables error:', e);
            toast('Error loading tables', 'error');
        }
    }

    function updateTableStatusBadge() {
        const occupied = allTables.filter(t => t.status === 'occupied').length;
        const total    = allTables.length;
        document.getElementById('tableStatusBadge').textContent = occupied + '/' + total + ' occupied';
    }

    function filterTables(section, btn) {
        tableFilter = section;
        const panel = document.querySelector('.tables-panel');
        if (panel) panel.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        renderTables();
    }

    // ═══════════════════════════════════════════
    // LIVE TABLE TIMERS
    // ═══════════════════════════════════════════

    let _tableTimerInterval = null;

    function formatElapsed(ms) {
        const totalSec = Math.floor(ms / 1000);
        const h   = Math.floor(totalSec / 3600);
        const m   = Math.floor((totalSec % 3600) / 60);
        const sec = totalSec % 60;
        if (h > 0) return h + 'h ' + String(m).padStart(2, '0') + 'm';
        if (m > 0) return m + 'm ' + String(sec).padStart(2, '0') + 's';
        return sec + 's';
    }

    function updateTableTimers() {
        const now = Date.now();
        document.querySelectorAll('.table-timer').forEach(function (el) {
            const at = new Date(el.dataset.occupiedAt).getTime();
            if (isNaN(at)) return;
            const ms    = now - at;
            const span  = el.querySelector('.elapsed-text');
            const badge = el.querySelector('.elapsed-badge');
            if (span)  span.textContent = formatElapsed(ms);
            if (badge) {
                if (ms > 7200000) {        // > 2 h → solid red
                    badge.style.background = '#dc2626';
                    badge.style.color      = '#fff';
                } else if (ms > 3600000) { // 1–2 h → solid amber
                    badge.style.background = '#f59e0b';
                    badge.style.color      = '#fff';
                } else {                   // < 1 h → dark translucent (safe on any card colour)
                    badge.style.background = 'rgba(0,0,0,0.25)';
                    badge.style.color      = '#fff';
                }
            }
        });
    }

    function startTableTimers() {
        clearInterval(_tableTimerInterval);
        updateTableTimers();                              // fire immediately
        _tableTimerInterval = setInterval(updateTableTimers, 1000); // then every second
    }

    function renderTables() {
        const container = document.getElementById('tablesContainer');
        const filtered  = tableFilter === 'all'
            ? allTables
            : allTables.filter(t => t.section === tableFilter);

        if (filtered.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#94a3b8; padding:32px 0; font-size:13px;">No tables found</p>';
            return;
        }

        container.innerHTML = filtered.map(function(table) {

            const isOccupied = table.status === 'occupied' || table.status === 'reserved';
            const isSelected = currentTable && currentTable.id === table.id;

            let itemBadge = '';
            if (table.has_order && table.order_items_count > 0) {
                itemBadge = '<div style="font-size:11px; font-weight:800; color:#dc2626; margin-top:4px;">'
                    + '<i class="fas fa-circle-dot" style="font-size:8px;"></i> '
                    + table.order_items_count + ' item' + (table.order_items_count !== 1 ? 's' : '')
                    + '</div>';
            }

            let timeLabel = '';
            if (table.occupied_at) {
                timeLabel = '<div class="table-timer" data-occupied-at="' + table.occupied_at + '" style="margin-top:5px;">'
                    + '<span class="elapsed-badge" style="display:inline-flex;align-items:center;gap:3px;'
                    + 'font-size:9px;font-weight:700;padding:2px 7px;border-radius:99px;'
                    + 'background:rgba(0,0,0,0.18);color:#fff;letter-spacing:0.01em;">'
                    + '<i class="fas fa-clock" style="font-size:8px;"></i>'
                    + '<span class="elapsed-text">—</span>'
                    + '</span>'
                    + '</div>';
            }

            let actionBar = '';
            if (isOccupied && table.has_order) {
                actionBar = '<div class="table-card-actions">'
                    + '<button onclick="printKotForTable(' + table.order_id + '); event.stopPropagation();" '
                    + 'style="flex:1; font-size:11px; font-weight:700; background:#ea580c; color:#fff; border:none; border-radius:7px; padding:6px 4px; cursor:pointer;">'
                    + '<i class="fas fa-print" style="margin-right:3px;"></i>KOT</button>'
                    + '</div>';
            }

            const clickFn = isOccupied && table.has_order
                ? 'viewTableOrder(' + table.order_id + ')'
                : (isOccupied ? 'expandTableCard(' + table.id + ', event)' : 'startNewOrder(' + table.id + ')');

            const vipBadge = table.section === 'vip'
                ? '<div style="position:absolute; top:6px; left:6px; font-size:9px; font-weight:800; background:#7c3aed; color:#fff; padding:2px 6px; border-radius:6px;">VIP</div>'
                : '';

            return '<div id="tc-' + table.id + '" class="table-card ' + table.status + (isSelected ? ' selected' : '') + '" onclick="' + clickFn + '">'
                + vipBadge
                + '<div style="font-size:20px; font-weight:900; color:#0f172a; line-height:1;">' + table.table_number + '</div>'
                + '<div style="font-size:11px; font-weight:600; color:#64748b; margin-top:2px;">' + escapeHtml(table.name) + '</div>'
                + '<div style="font-size:10px; color:#94a3b8;">Cap: ' + table.capacity + '</div>'
                + itemBadge + timeLabel + actionBar
                + '</div>';
        }).join('');

        startTableTimers();
    }

    function expandTableCard(tableId, event) {
        event.stopPropagation();
        const card       = document.getElementById('tc-' + tableId);
        const isExpanded = card.classList.contains('expanded');
        document.querySelectorAll('.table-card.expanded').forEach(function(c) { c.classList.remove('expanded'); });
        if (!isExpanded) {
            card.classList.add('expanded');
        }
    }

    async function viewTableOrder(orderId) {
        try {
            showLoading();
            const res   = await fetch('{{ route("pos.order.show", ":id") }}'.replace(':id', orderId));
            if (!res.ok) { toast('Failed to load order', 'error'); hideLoading(); return; }
            const order = await res.json();
            currentOrder = order;
            currentTable = allTables.find(function(t) { return t.id === order.table_id; }) || null;
            // Collapse all expanded cards, mark selected
            document.querySelectorAll('.table-card.expanded').forEach(function(c) { c.classList.remove('expanded'); });
            document.querySelectorAll('.table-card.selected').forEach(function(c) { c.classList.remove('selected'); });
            if (currentTable) {
                const card = document.getElementById('tc-' + currentTable.id);
                if (card) card.classList.add('selected');
            }
            renderTableView();
            renderBill();
            hideLoading();
        } catch (e) {
            console.error('View order error:', e);
            hideLoading();
            toast('Error loading order', 'error');
        }
    }

    async function startNewOrder(tableId) {
        const table = allTables.find(function(t) { return t.id === tableId; });
        if (!table) return;

        // If clicking the same table that's already selected and no items, deselect it
        if (currentTable && currentTable.id === tableId && (!currentOrder || !currentOrder.items || currentOrder.items.length === 0)) {
            resetOrder();
            await loadTables();
            return;
        }

        showLoading();
        currentTable = table;
        const res  = await fetch('{{ route("pos.order.create") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                table_id: tableId,
                order_type: 'dine_in'
            })
        });
        if (!res.ok) {
            hideLoading();
            toast('Failed to open table', 'error');
            return;
        }
        const data = await res.json();
        currentOrder = {
            id: data.order_id, order_number: data.order_number,
            items: [], subtotal: 0, total: 0,
            discount_amount: 0, live_bill_enabled: false,
            customer_name: null, customer_phone: null,
            table_id: tableId,
        };
        renderTableView();
        renderBill();
        await loadTables();
        hideLoading();
        toast('Table ' + table.table_number + ' opened', 'success');
    }

    async function startTakeawayOrder(forceType) {
        showLoading();
        try {
            // Deselect any previously selected table
            document.querySelectorAll('.table-card.selected').forEach(function(c) { c.classList.remove('selected'); });
            currentTable = null;

            let orderType = 'takeaway';
            if (typeof forceType === 'string' && ['takeaway', 'delivery', 'vip_room'].includes(forceType)) {
                orderType = forceType;
            }

            const selectEl = document.getElementById('orderTypeSelect');
            if (selectEl && selectEl.value !== orderType) {
                selectEl.value = orderType;
            }

            const res = await fetch('{{ route("pos.order.create") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    order_type: orderType
                })
            });

            if (!res.ok) {
                hideLoading();
                console.error('Order creation HTTP error. Status:', res.status, 'Text:', res.statusText);
                let errorMessage = 'Failed to create ' + orderType + ' order';

                if (res.status === 419) {
                    errorMessage = 'Session expired. Please reload the page and try again.';
                    console.error('CSRF/Session token error');
                } else if (res.status === 422) {
                    try {
                        const errorData = await res.json();
                        console.error('Validation errors:', errorData);
                        if (errorData.errors) {
                            errorMessage = Object.values(errorData.errors)[0][0] || errorMessage;
                        }
                    } catch (e) {
                        console.error('Could not parse error response');
                    }
                } else {
                    try {
                        const errorData = await res.json();
                        console.error('Order creation error:', errorData);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        console.error('Could not parse error response');
                    }
                }

                toast(errorMessage, 'error');
                return false;
            }

            let data;
            try {
                data = await res.json();
                console.log('Order created successfully:', data);
            } catch (e) {
                hideLoading();
                console.error('Failed to parse response JSON:', e);
                toast('Invalid server response. Please try again.', 'error');
                return false;
            }

            if (!data || !data.order_id) {
                hideLoading();
                console.error('Missing order_id in response:', data);
                toast('Failed to create ' + orderType + ' order: Invalid response from server', 'error');
                return false;
            }

            currentOrder = {
                id: data.order_id,
                order_number: data.order_number,
                items: [],
                subtotal: 0,
                total: 0,
                discount_amount: 0,
                live_bill_enabled: false,
                customer_name: null,
                customer_phone: null,
                table_id: null,
                order_type: orderType,
                table_number: null,
                table_name: null
            };

            renderTableView();
            renderBill();
            hideLoading();

            const typeLabel = orderType.charAt(0).toUpperCase() + orderType.slice(1);
            toast(typeLabel + ' order created — ready to add items', 'success');
            return true;
        } catch (e) {
            console.error('startTakeawayOrder error:', e);
            hideLoading();
            toast('Error creating order: ' + e.message, 'error');
            return false;
        }
    }

    // ═══════════════════════════════════════════
    // PRODUCTS
    // ═══════════════════════════════════════════

    async function loadProducts(search, categoryId) {
        try {
            search     = search     || '';
            categoryId = categoryId || 0;
            const params = new URLSearchParams();
            if (search)         params.append('search', search);
            if (categoryId > 0) params.append('category_id', categoryId);
            const res = await fetch('{{ route("pos.products") }}?' + params);
            if (!res.ok) { toast('Failed to load products', 'error'); return; }
            allProducts = await res.json();
            renderProducts();
        } catch (e) {
            console.error('Load products error:', e);
            toast('Error loading products', 'error');
        }
    }

    function loadCategories() {
        const container = document.getElementById('categoriesContainer');
        container.innerHTML = '<button class="cat-pill active" data-category="0" onclick="selectCategory(0, this)">All</button>';
        allCategories.forEach(function(cat) {
            const btn = document.createElement('button');
            btn.className = 'cat-pill';
            btn.textContent = cat.name;
            btn.setAttribute('data-category', cat.id);
            btn.onclick = function() { selectCategory(cat.id, btn); };
            container.appendChild(btn);
        });
    }

    function selectCategory(id, btn) {
        document.querySelectorAll('#categoriesContainer .cat-pill').forEach(function(b) { b.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        loadProducts(document.getElementById('searchInput').value, id);
    }

    function renderProducts() {
        const container = document.getElementById('productsContainer');
        if (allProducts.length === 0) {
            container.innerHTML = '<p style="grid-column:1/-1; text-align:center; color:#94a3b8; padding:48px 0; font-size:13px;"><i class="fas fa-search" style="font-size:28px; display:block; margin-bottom:10px;"></i>No products found</p>';
            return;
        }
        container.innerHTML = allProducts.map(function(p) {
            let imageHtml = '';
            if (p.image) {
                imageHtml = '<img src="/storage/' + p.image + '" alt="' + escapeHtml(p.name) + '" '
                    + 'style="width:100%; height:100%; object-fit:cover;">';
            } else {
                imageHtml = '<i class="fas fa-utensils" style="color:#dc2626; font-size:18px;"></i>';
            }

            return '<div class="product-card" onclick="addProductToOrder(' + p.id + ', \'' + escapeJs(p.name) + '\', ' + p.price + ')">'
                + '<div style="height:80px; background:linear-gradient(135deg,#fef2f2,#fee2e2); border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:10px; overflow:hidden; position:relative;">'
                + imageHtml
                + '</div>'
                + '<p style="font-size:12px; font-weight:700; color:#0f172a; margin:0 0 4px; line-height:1.3; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">' + escapeHtml(p.name) + '</p>'
                + '<p style="font-size:14px; font-weight:900; color:#dc2626; margin:0;">Rs. ' + p.price.toFixed(2) + '</p>'
                + '</div>';
        }).join('');
    }

    // ═══════════════════════════════════════════
    // ORDER MANAGEMENT
    // ═══════════════════════════════════════════

    async function addProductToOrder(productId, productName, price) {
        if (!currentOrder || !currentOrder.id) {
            const selectEl = document.getElementById('orderTypeSelect');
            const orderType = selectEl ? selectEl.value : 'dine_in';
            if (orderType === 'takeaway' || orderType === 'delivery' || orderType === 'vip_room') {
                const created = await startTakeawayOrder(orderType);
                if (!created) {
                    toast('Failed to create order. Please try again.', 'error');
                    return;
                }
                // Small delay to ensure order is created
                await new Promise(resolve => setTimeout(resolve, 100));
            } else {
                toast('Please select a table or create a takeaway order first', 'error');
                return;
            }
        }

        // Verify order is valid before adding items
        if (!currentOrder || !currentOrder.id || !Array.isArray(currentOrder.items)) {
            toast('No active order. Please create an order first.', 'error');
            return;
        }

        // Optimistic update
        const existing = currentOrder.items.find(function(i) { return i.product_id === productId; });
        if (existing) {
            existing.quantity++;
            existing.subtotal = existing.unit_price * existing.quantity;
        } else {
            currentOrder.items.push({
                id: null, product_id: productId, product_name: productName,
                unit_price: price, quantity: 1, subtotal: price, kitchen_notes: null
            });
        }
        recalcOrderTotals();
        renderBill();

        const res = await fetch('{{ route("pos.item.add", ":id") }}'.replace(':id', currentOrder.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        });
        const data = await res.json();
        if (data.success) {
            await syncOrder();
        } else {
            toast('Failed to add item to order', 'error');
        }
    }

    async function syncOrder() {
        try {
            if (!currentOrder || !currentOrder.id) return;
            const res = await fetch('{{ route("pos.order.show", ":id") }}'.replace(':id', currentOrder.id));
            if (!res.ok) return;
            currentOrder = await res.json();
            renderBill();
        } catch (e) {
            console.error('Sync order error:', e);
        }
    }

    async function increaseQty(itemId) {
        const item = currentOrder.items.find(function(i) { return i.id === itemId; });
        if (!item) return;
        item.quantity++;
        item.subtotal = item.unit_price * item.quantity;
        recalcOrderTotals();
        renderBill();
        await fetch('{{ route("pos.item.update", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', itemId), {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: item.quantity })
        });
        await syncOrder();
    }

    async function decreaseQty(itemId) {
        const item = currentOrder.items.find(function(i) { return i.id === itemId; });
        if (!item || item.quantity <= 1) return;
        item.quantity--;
        item.subtotal = item.unit_price * item.quantity;
        recalcOrderTotals();
        renderBill();
        await fetch('{{ route("pos.item.update", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', itemId), {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: item.quantity })
        });
        await syncOrder();
    }

    async function removeItem(itemId) {
        currentOrder.items = currentOrder.items.filter(function(i) { return i.id !== itemId; });
        recalcOrderTotals();
        renderBill();
        await fetch('{{ route("pos.item.remove", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', itemId), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        await syncOrder();
    }

    function recalcOrderTotals() {
        if (!currentOrder) return;
        const subtotal = currentOrder.items.reduce(function(s, i) { return s + i.subtotal; }, 0);
        currentOrder.subtotal = subtotal;
        currentOrder.total    = subtotal;
        currentOrder.discount_amount = 0;
    }

    // ═══════════════════════════════════════════
    // BILL PANEL RENDER
    // ═══════════════════════════════════════════

    function renderTableView() {
        if (!currentTable && !currentOrder) {
            document.getElementById('selectedTableLabel').innerHTML =
                '<i class="fas fa-arrow-left" style="font-size:11px; margin-right:4px;"></i>Select a table or create takeaway order';
            document.getElementById('customerInfoToggle').style.display = 'none';
            document.getElementById('activeOrderBanner').style.display   = 'none';
            return;
        }

        if (!currentTable && currentOrder) {
            const displayType = currentOrder.order_type ? 
                                currentOrder.order_type.charAt(0).toUpperCase() + currentOrder.order_type.slice(1) : 
                                'Takeaway';
                                
            document.getElementById('selectedTableLabel').innerHTML =
                '🛍 <strong>' + displayType + ' Order</strong> — ' + (currentOrder.order_number || '—');
            document.getElementById('customerInfoToggle').style.display = 'flex';
            document.getElementById('activeOrderBanner').style.display   = 'flex';
            document.getElementById('activeOrderText').textContent = displayType + ' — adding items';
            return;
        }

        const sectionLabel = currentTable.section === 'vip' ? '🟣 VIP' : '🍽';
        document.getElementById('selectedTableLabel').innerHTML =
            sectionLabel + ' <strong>Table ' + currentTable.table_number + '</strong> — ' + escapeHtml(currentTable.name);
        document.getElementById('customerInfoToggle').style.display = 'flex';
        document.getElementById('activeOrderBanner').style.display   = 'flex';
        document.getElementById('activeOrderText').textContent = 'Adding to Table ' + currentTable.table_number;

        if (currentOrder) {
            if (currentOrder.customer_id) {
                const tier = currentOrder.customer?.tier || 'New';
                selectCustomer(
                    currentOrder.customer_id,
                    currentOrder.customer_name  || '',
                    currentOrder.customer_phone || '',
                    tier
                );
            } else {
                document.getElementById('customerName').value  = currentOrder.customer_name  || '';
                document.getElementById('customerPhone').value = currentOrder.customer_phone || '';
                document.getElementById('selectedCustomerChip').style.display = 'none';
                document.getElementById('customerSearchInputs').style.display = 'grid';
            }
        }
    }

    function renderBill() {
        if (!currentOrder || !currentOrder.items) {
            document.getElementById('billItems').innerHTML = '<div style="text-align:center; padding:48px 0; color:#cbd5e1;"><i class="fas fa-utensils" style="font-size:36px; margin-bottom:12px; display:block;"></i><p style="font-size:13px; margin:0;">Select a table or create takeaway order</p></div>';
            setBottomControls(false);
            updateCloseButtonVisibility(false);
            return;
        }

        const hasItems = currentOrder.items && currentOrder.items.length > 0;

        if (!hasItems) {
            document.getElementById('billItems').innerHTML =
                '<p style="text-align:center; color:#94a3b8; font-size:13px; padding:32px 0;"><i class="fas fa-plus-circle" style="display:block; font-size:24px; margin-bottom:8px;"></i>No items yet — tap a product</p>';
        } else {
            document.getElementById('billItems').innerHTML = currentOrder.items.map(function(item) {
                const noteHtml = item.kitchen_notes
                    ? '<p style="font-size:10px; color:#f59e0b; margin:2px 0 0;"><i class="fas fa-note-sticky" style="margin-right:3px;"></i>' + escapeHtml(item.kitchen_notes) + '</p>'
                    : '';
                const removeBtn = item.id
                    ? '<button onclick="removeItem(' + item.id + ')" style="font-size:10px; color:#ef4444; background:none; border:none; cursor:pointer; padding:0; margin-top:3px;"><i class="fas fa-trash"></i> Remove</button>'
                    : '';
                const decBtn = item.id
                    ? '<button class="qty-btn" onclick="decreaseQty(' + item.id + ')">−</button>'
                    : '<button class="qty-btn" style="opacity:0.4;" disabled>−</button>';
                const incBtn = item.id
                    ? '<button class="qty-btn" onclick="increaseQty(' + item.id + ')">+</button>'
                    : '<button class="qty-btn" style="opacity:0.4;" disabled>+</button>';

                let thumbHtml = '';
                if (item.image) {
                    thumbHtml = '<div style="width:48px; height:48px; border-radius:8px; overflow:hidden; flex-shrink:0; background:#f1f5f9; margin-right:10px;">'
                        + '<img src="/storage/' + item.image + '" alt="' + escapeHtml(item.product_name) + '" '
                        + 'style="width:100%; height:100%; object-fit:cover;">'
                        + '</div>';
                }

                return '<div class="bill-item" style="align-items:flex-start;">'
                    + thumbHtml
                    + '<div style="flex:1; min-width:0;">'
                    + '<p style="font-size:13px; font-weight:700; color:#0f172a; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + escapeHtml(item.product_name) + '</p>'
                    + '<p style="font-size:11px; color:#94a3b8; margin:2px 0 0;">Rs. ' + item.unit_price.toFixed(2) + ' each</p>'
                    + noteHtml
                    + '</div>'
                    + '<div style="display:flex; align-items:center; gap:5px; flex-shrink:0;">'
                    + decBtn
                    + '<span style="min-width:22px; text-align:center; font-size:13px; font-weight:800; color:#0f172a;">' + item.quantity + '</span>'
                    + incBtn
                    + '</div>'
                    + '<div style="min-width:72px; text-align:right; flex-shrink:0;">'
                    + '<p style="font-size:13px; font-weight:800; color:#0f172a; margin:0;">Rs. ' + item.subtotal.toFixed(2) + '</p>'
                    + removeBtn
                    + '</div>'
                    + '</div>';
            }).join('');
        }

        // Totals
        const subtotal = currentOrder.subtotal || 0;
        const discount = calcDiscount(subtotal);
        const total    = Math.max(0, subtotal - discount);

        document.getElementById('subtotalDisplay').textContent = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('totalDisplay').textContent    = 'Rs. ' + total.toFixed(2);

        setBottomControls(hasItems);
        updateCloseButtonVisibility(hasItems);
        updateChange();
        scrollBillToBottom();
    }

    function updateCloseButtonVisibility(hasItems) {
        const closeBtn = document.getElementById('closeOrderBtn');
        if (closeBtn) {
            closeBtn.style.display = hasItems ? 'none' : 'flex';
        }
    }

    function setBottomControls(hasItems) {
        document.getElementById('paymentSection').style.display     = hasItems ? 'block' : 'none';
        document.getElementById('waiterPayRow').style.display       = hasItems ? 'flex' : 'none';
        document.getElementById('holdBtn').style.display            = hasItems ? 'block' : 'none';
    }

    function toggleCustomerInfo() {
        const section = document.getElementById('customerInfoSection');
        const toggle = document.getElementById('customerInfoToggle');
        const chevron = document.getElementById('customerInfoChevron');
        const isOpen = section.style.display !== 'none';

        section.style.display = isOpen ? 'none' : 'block';
        toggle.style.background = isOpen ? 'none' : '#f0fdf4';
        chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }


    function scrollBillToBottom() {
        const wrapper = document.getElementById('billItemsWrapper');
        wrapper.scrollTop = wrapper.scrollHeight;
    }

    // ═══════════════════════════════════════════
    // CUSTOMER INFO — search autocomplete
    // ═══════════════════════════════════════════

    let _customerSearchTimer = null;
    let _selectedCustomerId  = null;

    // Tier discount map injected from PHP — e.g. { VIP: 15, Moderate: 10, ... }
    const TIER_DISCOUNTS = @json($tierDiscounts ?? []);

    const TIER_COLORS = {
        'VIP':      { bg: '#fef9c3', color: '#854d0e', border: '#fde047' },
        'Moderate': { bg: '#dbeafe', color: '#1e40af', border: '#93c5fd' },
        'Medium':   { bg: '#f3e8ff', color: '#6b21a8', border: '#c084fc' },
        'Small':    { bg: '#f1f5f9', color: '#475569', border: '#94a3b8' },
        'New':      { bg: '#dcfce7', color: '#166534', border: '#86efac' },
    };

    function onCustomerInput(field) {
        clearTimeout(_customerSearchTimer);
        const q = document.getElementById(field === 'name' ? 'customerName' : 'customerPhone').value.trim();
        if (q.length < 1) { hideCustomerDropdown(); return; }
        _customerSearchTimer = setTimeout(() => searchCustomers(q), 220);
    }

    async function searchCustomers(q) {
        const res  = await fetch('{{ route("customers.search") }}?q=' + encodeURIComponent(q), {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const list = await res.json();
        renderCustomerDropdown(list);
    }

    function renderCustomerDropdown(customers) {
        const dd = document.getElementById('customerDropdown');
        if (!customers.length) { hideCustomerDropdown(); return; }

        dd.innerHTML = customers.map(c => {
            const tier   = c.tier || 'New';
            const tc     = TIER_COLORS[tier] || TIER_COLORS['New'];
            const title  = c.title ? c.title + ' ' : '';
            return `<div class="cust-result" onclick="selectCustomer(${c.id}, '${escapeHtml(title + c.name)}', '${escapeHtml(c.phone_number)}', '${tier}')"
                        style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; cursor:pointer; border-bottom:1px solid #f1f5f9; font-size:11px;"
                        onmouseenter="this.style.background='#eff6ff'" onmouseleave="this.style.background='#fff'">
                <div>
                    <div style="font-weight:600; color:#1e293b;">${escapeHtml(title + c.name)}</div>
                    <div style="color:#64748b; font-size:10px;">${escapeHtml(c.phone_number)}</div>
                </div>
                <span style="font-size:9px; font-weight:700; padding:2px 7px; border-radius:99px;
                             background:${tc.bg}; color:${tc.color}; border:1px solid ${tc.border};">${tier}</span>
            </div>`;
        }).join('');

        dd.style.display = 'block';
    }

    function hideCustomerDropdown() {
        document.getElementById('customerDropdown').style.display = 'none';
    }

    function selectCustomer(id, name, phone, tier) {
        _selectedCustomerId = id;

        // Fill inputs (used as fallback / display value)
        document.getElementById('customerName').value  = name;
        document.getElementById('customerPhone').value = phone;

        // Show chip
        const tc = TIER_COLORS[tier] || TIER_COLORS['New'];
        document.getElementById('chipName').textContent  = name;
        document.getElementById('chipPhone').textContent = phone;
        const chipTier = document.getElementById('chipTier');
        chipTier.textContent      = tier;
        chipTier.style.background  = tc.bg;
        chipTier.style.color       = tc.color;
        chipTier.style.borderColor = tc.border;

        document.getElementById('selectedCustomerChip').style.display = 'flex';
        document.getElementById('customerSearchInputs').style.display = 'none';
        hideCustomerDropdown();

        // Auto-apply tier discount
        applyTierDiscount(tier);

        saveCustomerInfo();
    }

    function applyTierDiscount(tier) {
        const pct = TIER_DISCOUNTS[tier] ?? 0;
        const discountTypeEl  = document.getElementById('discountType');
        const discountValueEl = document.getElementById('discountValue');
        const discountBadge   = document.getElementById('tierDiscountBadge');

        if (pct > 0) {
            discountTypeEl.value  = 'percentage';
            discountValueEl.value = pct;
            if (discountBadge) {
                discountBadge.textContent = tier + ' discount: ' + pct + '% applied';
                discountBadge.style.display = 'flex';
            }
        } else {
            // Tier has 0% — clear any tier-applied discount but don't wipe a manual one
            if (discountBadge && discountBadge.style.display !== 'none') {
                discountTypeEl.value  = '';
                discountValueEl.value = '';
                discountBadge.style.display = 'none';
            }
        }
        recalcTotal();
    }

    function clearSelectedCustomer() {
        _selectedCustomerId = null;
        document.getElementById('selectedCustomerChip').style.display = 'none';
        document.getElementById('customerSearchInputs').style.display = 'grid';
        document.getElementById('customerName').value  = '';
        document.getElementById('customerPhone').value = '';

        // Remove the tier discount that was auto-applied
        const discountBadge = document.getElementById('tierDiscountBadge');
        if (discountBadge && discountBadge.style.display !== 'none') {
            document.getElementById('discountType').value  = '';
            document.getElementById('discountValue').value = '';
            discountBadge.style.display = 'none';
            recalcTotal();
        }

        saveCustomerInfo();
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#customerInfoSection')) hideCustomerDropdown();
    });

    async function saveCustomerInfo() {
        if (!currentOrder || !currentOrder.id) return;
        const name  = document.getElementById('customerName').value.trim();
        const phone = document.getElementById('customerPhone').value.trim();
        if (currentOrder.customer_name === name && currentOrder.customer_phone === phone && currentOrder.customer_id === _selectedCustomerId) return;
        await fetch('{{ route("pos.order.customer", ":id") }}'.replace(':id', currentOrder.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ customer_id: _selectedCustomerId, customer_name: name, customer_phone: phone })
        });
        currentOrder.customer_name  = name;
        currentOrder.customer_phone = phone;
        currentOrder.customer_id    = _selectedCustomerId;
    }

    // ═══════════════════════════════════════════
    // PAYMENT
    // ═══════════════════════════════════════════

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        document.querySelectorAll('.pay-method-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.method === method);
        });
        document.getElementById('cashSection').style.display = method === 'cash' ? 'flex' : 'none';
        if (method !== 'cash') document.getElementById('changeDisplay').textContent = 'Rs. 0.00';
    }

    function calcDiscount(subtotal) {
        const type  = document.getElementById('discountType').value;
        const value = parseFloat(document.getElementById('discountValue').value) || 0;
        if (type === 'percentage') return (subtotal * value) / 100;
        if (type === 'fixed')      return value;
        return 0;
    }

    function recalcTotal() {
        if (!currentOrder) return;
        const subtotal = currentOrder.subtotal || 0;
        const discount = calcDiscount(subtotal);
        document.getElementById('totalDisplay').textContent = 'Rs. ' + Math.max(0, subtotal - discount).toFixed(2);
        updateChange();
    }

    function updateChange() {
        if (selectedPaymentMethod !== 'cash') return;
        const subtotal = currentOrder ? (currentOrder.subtotal || 0) : 0;
        const discount = calcDiscount(subtotal);
        const total    = Math.max(0, subtotal - discount);
        const paid     = parseFloat(document.getElementById('amountPaid').value) || 0;
        const change   = Math.max(0, paid - total);
        const el       = document.getElementById('changeDisplay');
        el.textContent = 'Rs. ' + change.toFixed(2);
        el.style.color = change > 0 ? '#16a34a' : '#94a3b8';
    }

    async function initiatePayment() {
        if (!currentOrder || !currentOrder.id || !currentOrder.items || !currentOrder.items.length) {
            toast('No items in order', 'error'); return;
        }
        await saveCustomerInfo();

        const subtotal    = currentOrder.subtotal || 0;
        const discountVal = calcDiscount(subtotal);
        const total       = Math.max(0, subtotal - discountVal);
        const amountPaid  = selectedPaymentMethod === 'cash'
            ? (parseFloat(document.getElementById('amountPaid').value) || total)
            : total;

        const res = await fetch('{{ route("pos.order.pay", ":id") }}'.replace(':id', currentOrder.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                payment_method: selectedPaymentMethod,
                amount_paid:    amountPaid,
                discount_type:  document.getElementById('discountType').value || null,
                discount_value: parseFloat(document.getElementById('discountValue').value) || 0,
            })
        });
        if (!res.ok) {
            toast('Payment failed — server error', 'error');
            return;
        }
        const data = await res.json();
        if (data.success) {
            showPaidBill(data);
            printBillContent();
            await loadTables();
            toast('Payment received & bill printed — table closed!', 'success');
        } else {
            toast(data.error || 'Payment failed', 'error');
        }
    }

    function showPaidBill(d) {
        const methodLabel = { cash:'Cash', card:'Card', bank_transfer:'Bank Transfer', mixed:'Mixed' };
        const itemRows = d.items.map(function(i) {
            return '<div style="display:flex; justify-content:space-between; font-size:12px; margin:5px 0;">'
                + '<span>' + escapeHtml(i.product_name) + ' × ' + i.quantity + '</span>'
                + '<span>Rs. ' + i.subtotal.toFixed(2) + '</span></div>';
        }).join('');

        const html = '<div style="text-align:center; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:12px;">'
            + '<div style="font-weight:900; font-size:15px; letter-spacing:1px; color:#000;">RESTAURANT BYOB</div>'
            + '<div style="font-size:11px; margin-top:3px; color:#000;">Order: ' + d.order_number + '</div>'
            + '<div style="font-size:12px; font-weight:700; color:#000;">Table ' + d.table_number + (d.table_name ? ' — ' + escapeHtml(d.table_name) : '') + '</div>'
            + (d.customer_name  ? '<div style="font-size:11px; color:#000;">Customer: ' + escapeHtml(d.customer_name) + '</div>' : '')
            + (d.customer_phone ? '<div style="font-size:11px; color:#000;">Phone: ' + d.customer_phone + '</div>' : '')
            + '<div style="font-size:10px; color:#000;">' + new Date().toLocaleString() + '</div></div>'
            + itemRows
            + '<div style="border-top:2px solid #000; margin-top:10px; padding-top:10px;">'
            + '<div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:3px; color:#000;"><span>Subtotal</span><span>Rs. ' + d.subtotal.toFixed(2) + '</span></div>'
            + (d.discount_amount > 0 ? '<div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:3px; color:#000;"><span>Discount</span><span>-Rs. ' + d.discount_amount.toFixed(2) + '</span></div>' : '')
            + '<div style="display:flex; justify-content:space-between; font-weight:900; font-size:14px; margin-top:5px; color:#000;"><span>Total</span><span>Rs. ' + d.total.toFixed(2) + '</span></div>'
            + '<div style="display:flex; justify-content:space-between; font-size:12px; margin-top:5px; color:#000;"><span>Paid (' + (methodLabel[d.payment_method] || d.payment_method) + ')</span><span>Rs. ' + d.amount_paid.toFixed(2) + '</span></div>'
            + (d.change_amount > 0 ? '<div style="display:flex; justify-content:space-between; font-size:12px; color:#000;"><span>Change</span><span>Rs. ' + d.change_amount.toFixed(2) + '</span></div>' : '')
            + '</div>'
            + '<div style="text-align:center; margin-top:14px; border:2px solid #16a34a; border-radius:6px; padding:7px; font-weight:900; font-size:15px; color:#16a34a; letter-spacing:2px;">✓ PAID ✓</div>'
            + '<div style="text-align:center; font-size:10px; margin-top:10px; color:#000;">Thank you for dining with us!</div>';

        currentBillContent = html;
        document.getElementById('billContent').innerHTML = html;
        openModal('finalBillModal');
        resetOrder();
    }

    // ═══════════════════════════════════════════
    // BILL
    // ═══════════════════════════════════════════

    async function printBill() {
        if (!currentOrder || !currentOrder.id) return;
        await saveCustomerInfo();
        const res  = await fetch('{{ route("pos.order.waiter_bill", ":id") }}'.replace(':id', currentOrder.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        if (!data.success) { toast('Could not generate bill', 'error'); return; }

        const itemRows = data.items.map(function(i) {
            return '<div style="margin:6px 0; font-size:12px;">'
                + '<div style="display:flex; justify-content:space-between;">'
                + '<span style="font-weight:700;">' + escapeHtml(i.product_name) + '</span>'
                + '<span>Rs. ' + i.subtotal.toFixed(2) + '</span></div>'
                + '<div style="font-size:10px; color:#555; text-align:right;">' + i.quantity + ' × Rs. ' + i.unit_price.toFixed(2) + '</div>'
                + (i.kitchen_notes ? '<div style="font-size:10px; color:#888; font-style:italic;">Note: ' + escapeHtml(i.kitchen_notes) + '</div>' : '')
                + '</div>';
        }).join('');

        const html = '<div style="text-align:center; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:12px;">'
            + '<div style="font-weight:900; font-size:15px; color:#000;">RESTAURANT BYOB</div>'
            + '<div style="font-size:14px; font-weight:800; margin:4px 0;">— WAITER BILL —</div>'
            + '<div style="font-size:13px; font-weight:700; background:#000; color:#fff; padding:3px 12px; display:inline-block; border-radius:4px; margin:4px 0;">Table ' + data.table_number + '</div>'
            + '<div style="font-size:11px; margin-top:4px; color:#000;">Order: ' + data.order_number + '</div>'
            + (data.customer_name  ? '<div style="font-size:11px; color:#000;">Customer: ' + escapeHtml(data.customer_name) + '</div>' : '')
            + (data.customer_phone ? '<div style="font-size:11px; color:#000;">Phone: ' + data.customer_phone + '</div>' : '')
            + '<div style="font-size:10px; color:#000;">' + new Date().toLocaleString() + '</div></div>'
            + itemRows
            + '<div style="border-top:2px solid #000; margin-top:10px; padding-top:10px;">'
            + '<div style="display:flex; justify-content:space-between; font-size:12px; color:#000;"><span>Subtotal</span><span>Rs. ' + data.subtotal.toFixed(2) + '</span></div>'
            + '<div style="display:flex; justify-content:space-between; font-weight:900; font-size:14px; margin-top:5px; color:#000;"><span>Total</span><span>Rs. ' + data.total.toFixed(2) + '</span></div>'
            + '</div>'
            + '<div style="text-align:center; font-size:10px; margin-top:10px; color:#000; border-top:1px dashed #ccc; padding-top:8px;">This is not a payment receipt</div>';

        printReceipt(html);
        toast('Waiter bill printed', 'success');
    }


    // ═══════════════════════════════════════════
    // KOT
    // ═══════════════════════════════════════════

    async function printKot() {
        if (!currentOrder || !currentOrder.id) { toast('No active order', 'error'); return; }
        const res  = await fetch('{{ route("pos.order.kot", ":id") }}'.replace(':id', currentOrder.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        document.getElementById('kotOrderNumber').textContent = 'Order #' + data.order_number;
        document.getElementById('kotTableNumber').textContent = 'Table ' + (currentTable ? currentTable.table_number : '—');
        renderKotItems(data.items);
        currentKotContent = buildKotHtml(data, currentTable ? currentTable.table_number : '—');
        openModal('kotModal');
    }

    async function printKotForTable(orderId) {
        const res  = await fetch('{{ route("pos.order.kot", ":id") }}'.replace(':id', orderId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        document.getElementById('kotOrderNumber').textContent = 'Order #' + data.order_number;
        document.getElementById('kotTableNumber').textContent = 'Table ' + (data.table_number || '—');
        renderKotItems(data.items);
        currentKotContent = buildKotHtml(data, data.table_number || '—');
        openModal('kotModal');
    }

    function renderKotItems(items) {
        document.getElementById('kotItems').innerHTML = items.map(function(item) {
            return '<div style="display:flex; justify-content:space-between; align-items:flex-start; padding:8px 0; border-bottom:1px dashed #e2e8f0;">'
                + '<div>'
                + '<p style="font-size:14px; font-weight:800; margin:0; color:#0f172a;">' + escapeHtml(item.product_name) + '</p>'
                + (item.kitchen_notes ? '<p style="font-size:11px; color:#f59e0b; margin:3px 0 0;"><i class="fas fa-note-sticky" style="margin-right:3px;"></i>' + escapeHtml(item.kitchen_notes) + '</p>' : '')
                + '</div>'
                + '<span style="font-size:18px; font-weight:900; color:#dc2626; margin-left:12px;">×' + item.quantity + '</span>'
                + '</div>';
        }).join('');
    }

    function buildKotHtml(data, tableNum) {
        return '<div style="text-align:center; font-weight:900; font-size:16px; border-bottom:2px solid #000; padding-bottom:8px; margin-bottom:10px; color:#000;">KITCHEN ORDER</div>'
            + '<div style="font-size:13px; font-weight:800; color:#000;">Order: ' + data.order_number + '</div>'
            + '<div style="font-size:14px; font-weight:900; margin:4px 0; color:#000;">Table ' + tableNum + '</div>'
            + '<div style="font-size:10px; color:#000; margin-bottom:10px;">' + new Date().toLocaleString() + '</div>'
            + '<div style="border-top:1px solid #000; padding-top:10px;">'
            + data.items.map(function(i) {
                return '<div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; margin:8px 0; border-bottom:1px dashed #000; padding-bottom:6px; color:#000;">'
                    + '<span>' + escapeHtml(i.product_name) + '</span>'
                    + '<span style="font-size:16px; font-weight:900;">×' + i.quantity + '</span>'
                    + '</div>'
                    + (i.kitchen_notes ? '<div style="font-size:11px; color:#000; margin-top:-4px; margin-bottom:6px;">Note: ' + escapeHtml(i.kitchen_notes) + '</div>' : '');
            }).join('')
            + '</div>';
    }

    function printKotContent() {
        printReceipt(currentKotContent);
        closeModal('kotModal');
    }

    function printBillContent() {
        printReceipt(currentBillContent);
    }

    // ═══════════════════════════════════════════
    // HELD ORDERS
    // ═══════════════════════════════════════════

    async function holdCurrentOrder() {
        if (!currentOrder || !currentOrder.id) return;
        await fetch('{{ route("pos.order.hold", ":id") }}'.replace(':id', currentOrder.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        toast('Order held');
        resetOrder();
        loadHeldOrders();
    }

    async function loadHeldOrders() {
        try {
            const res    = await fetch('{{ route("pos.held") }}');
            if (!res.ok) { toast('Failed to load held orders', 'error'); return; }
            const orders = await res.json();
            const badge  = document.getElementById('heldCount');
            badge.textContent = orders.length;
            badge.style.background = orders.length > 0 ? '#f59e0b' : '#94a3b8';

            const list = document.getElementById('heldOrdersList');
            if (orders.length === 0) {
                list.innerHTML = '<p style="text-align:center; color:#94a3b8; padding:32px 0; font-size:13px;">No held orders</p>';
            } else {
                list.innerHTML = orders.map(function(o) {
                    return '<div onclick="resumeOrder(' + o.id + ')" '
                        + 'style="padding:14px; border:1.5px solid #e2e8f0; border-radius:12px; cursor:pointer; transition:all 0.15s; background:#fff;" '
                        + 'onmouseover="this.style.borderColor=\'#dc2626\'; this.style.background=\'#fef2f2\';" '
                        + 'onmouseout="this.style.borderColor=\'#e2e8f0\'; this.style.background=\'#fff\';">'
                        + '<div style="display:flex; justify-content:space-between; align-items:flex-start;">'
                        + '<div>'
                        + '<p style="font-size:13px; font-weight:800; color:#0f172a; margin:0;">' + o.order_number + '</p>'
                        + '<p style="font-size:12px; color:#64748b; margin:3px 0 0;">Table ' + (o.table_number || '—') + ' &nbsp;&middot;&nbsp; ' + o.items_count + ' item' + (o.items_count !== 1 ? 's' : '') + '</p>'
                        + '</div>'
                        + '<span style="font-size:14px; font-weight:900; color:#dc2626;">Rs. ' + o.total.toFixed(2) + '</span>'
                        + '</div></div>';
                }).join('');
            }
            openModal('heldOrdersModal');
        } catch (e) {
            console.error('Load held orders error:', e);
            toast('Error loading held orders', 'error');
        }
    }

    async function resumeOrder(orderId) {
        const res    = await fetch('{{ route("pos.order.show", ":id") }}'.replace(':id', orderId));
        currentOrder = await res.json();
        currentTable = allTables.find(function(t) { return t.id === currentOrder.table_id; }) || null;
        renderTableView();
        renderBill();
        closeModal('heldOrdersModal');
        await loadTables();
        toast('Order resumed');
    }

    async function closeCurrentOrder() {
        if (!currentOrder || !currentOrder.id) return;
        if (currentOrder.items && currentOrder.items.length > 0) {
            if (!confirm('This order has items. Close anyway and discard all items?')) return;
        }

        // Call backend to cancel the order and free the table
        try {
            const res = await fetch('{{ route("pos.order.close_table", ":id") }}'.replace(':id', currentOrder.id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            if (!res.ok) {
                toast('Failed to close table', 'error');
                return;
            }
        } catch (e) {
            console.error('Close order error:', e);
            toast('Error closing table', 'error');
            return;
        }

        resetOrder();
        await loadTables();
        toast('Table deselected', 'success');
    }

    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════

    function resetOrder() {
        currentOrder = null;
        currentTable = null;
        selectedPaymentMethod = 'cash';

        document.getElementById('billItems').innerHTML = '<div style="text-align:center; padding:48px 0; color:#cbd5e1;"><i class="fas fa-utensils" style="font-size:36px; margin-bottom:12px; display:block;"></i><p style="font-size:13px; margin:0;">Select a table or create takeaway order</p></div>';
        document.getElementById('selectedTableLabel').innerHTML = '<i class="fas fa-arrow-left" style="font-size:11px; margin-right:4px;"></i>Select a table or create takeaway order';
        document.getElementById('customerInfoToggle').style.display     = 'none';
        document.getElementById('customerInfoSection').style.display    = 'none';
        document.getElementById('activeOrderBanner').style.display      = 'none';
        document.getElementById('paymentSection').style.display         = 'none';
        document.getElementById('waiterPayRow').style.display           = 'none';
        document.getElementById('holdBtn').style.display                = 'none';
        document.getElementById('confirmLiveBillBtn').style.display     = 'none';
        document.getElementById('customerName').value   = '';
        document.getElementById('customerPhone').value  = '';
        _selectedCustomerId = null;
        document.getElementById('selectedCustomerChip').style.display = 'none';
        document.getElementById('customerSearchInputs').style.display = 'grid';
        document.getElementById('discountType').value   = '';
        document.getElementById('discountValue').value  = '';
        document.getElementById('amountPaid').value     = '';
        document.getElementById('changeDisplay').textContent  = 'Rs. 0.00';
        document.getElementById('subtotalDisplay').textContent = 'Rs. 0.00';
        document.getElementById('totalDisplay').textContent    = 'Rs. 0.00';
        document.querySelectorAll('.pay-method-btn').forEach(function(b) {
            b.classList.toggle('active', b.dataset.method === 'cash');
        });
        document.getElementById('cashSection').style.display = 'flex';
        document.querySelectorAll('.table-card.expanded').forEach(function(c) { c.classList.remove('expanded'); });
        document.querySelectorAll('.table-card.selected').forEach(function(c) { c.classList.remove('selected'); });
        loadTables();
    }

    function printReceipt(html) {
        const w = window.open('', '', 'width=400,height=700,toolbar=0,menubar=0,scrollbars=1');
        w.document.write('<!DOCTYPE html><html><head><style>body{font-family:\'Courier New\',monospace;width:80mm;padding:10px;margin:0;font-size:12px;}</style></head><body>' + html + '</body></html>');
        w.document.close();
        w.focus();
        w.print();
        setTimeout(function() { w.close(); }, 1200);
    }

    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    function showLoading() { document.body.style.cursor = 'wait'; }
    function hideLoading() { document.body.style.cursor = 'default'; }

    function toast(message, type) {
        type = type || '';
        const el = document.getElementById('toast');
        el.textContent = message;
        el.className   = 'show' + (type ? ' ' + type : '');
        clearTimeout(el._t);
        el._t = setTimeout(function() { el.className = ''; }, 2800);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function escapeJs(str) {
        if (!str) return '';
        return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"');
    }

    function setupEventListeners() {
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const cat = document.querySelector('#categoriesContainer .cat-pill.active');
            loadProducts(e.target.value, cat ? parseInt(cat.getAttribute('data-category')) : 0);
        });
        document.getElementById('discountValue').addEventListener('input', recalcTotal);
        document.getElementById('discountType').addEventListener('change', recalcTotal);

        // Close modal on backdrop click
        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) overlay.classList.remove('open');
            });
        });
    }

    window.addEventListener('load', initPos);
</script>
</body>
</html>
