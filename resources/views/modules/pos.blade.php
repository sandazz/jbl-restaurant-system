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
        .product-card--disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
        .product-card--disabled:hover { border-color: #e2e8f0; box-shadow: none; transform: none; }
        .stock-badge {
            position: absolute; top: 6px; right: 6px;
            font-size: 10px; font-weight: 700; padding: 2px 6px;
            border-radius: 999px; background: #f1f5f9; color: #0f172a;
        }
        .stock-badge.in  { background: #dcfce7; color: #166534; }
        .stock-badge.out { background: #fee2e2; color: #b91c1c; }

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

        /* ── Receipt classes — scoped to #billContent so modal renders correctly ── */
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
        #billContent .item-name      { flex: 1; word-break: break-word; }
        #billContent .item-qty       { width: 28px; text-align: center; flex-shrink: 0; }
        #billContent .item-amt       { width: 70px; text-align: right; flex-shrink: 0; }
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
                    <i class="fas fa-ticket-alt" style="color:#dc2626; margin-right:6px;"></i>Tokens
                </h2>
                <button onclick="loadOrderHistory()" title="View order history" style="background:#f3f4f6; color:#6b7280; border:none; border-radius:6px; padding:6px 8px; cursor:pointer; font-size:11px; font-weight:600; transition:all 0.2s;" onmouseover="this.style.background='#e5e7eb'; this.style.color='#374151';" onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280';">
                    <i class="fas fa-history" style="margin-right:4px;"></i>History
                </button>
            </div>
            <!-- Filter tabs -->
            <div style="display:flex; gap:6px; margin-bottom:10px;">
                <button onclick="filterTables('all', this)" class="cat-pill active" style="padding:4px 12px;">All</button>
                <button onclick="filterTables('main', this)" class="cat-pill" style="padding:4px 12px;">Main</button>
                <button onclick="filterTables('vip', this)" class="cat-pill" style="padding:4px 12px;">VIP</button>
            </div>
            <!-- Buttons Row -->
            <div style="display:flex; gap:8px;">
                <button onclick="createQuickToken()" class="btn-primary" style="flex:1; padding:10px; font-size:12px; font-weight:700;">
                    <i class="fas fa-plus" style="margin-right:4px;"></i>New Token
                </button>
                <button onclick="startTakeawayOrder()" class="btn-primary" style="flex:1; padding:10px; font-size:12px; font-weight:700;">
                    <i class="fas fa-shopping-bag" style="margin-right:4px;"></i>Takeaway
                </button>
            </div>
        </div>

        <!-- Tokens list -->
        <div style="flex:1; overflow-y:auto; padding:12px; display:flex; flex-direction:column; gap: 8px;" id="tablesContainer">
            <p style="text-align:center; color:#94a3b8; padding:32px 12px; font-size:13px;">Loading tokens…</p>
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
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                <div id="selectedTableLabel" style="font-size:12px; font-weight:700; color:#64748b; flex:1;">
                    <i class="fas fa-arrow-left" style="font-size:10px; margin-right:4px;"></i>Select a table to begin
                </div>
                <button id="cancelTokenBtn" onclick="cancelToken()" style="display:none; font-size:10px; font-weight:700; background:#fef2f2; color:#dc2626; border:1.5px solid #fecaca; border-radius:6px; padding:4px 8px; cursor:pointer; white-space:nowrap; transition:all 0.15s;" onmouseover="this.style.background='#dc2626'; this.style.color='#fff';" onmouseout="this.style.background='#fef2f2'; this.style.color='#dc2626';">
                    <i class="fas fa-times-circle" style="margin-right:3px;"></i>Cancel Token
                </button>
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
                <div id="customerSearchInputs" style="display:grid; grid-template-columns:1fr 1fr 40px; gap:6px; position:relative;">
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
                    <button onclick="openCreateCustomerModal()" title="Create new customer" style="background:#16a34a; color:#fff; border:none; border-radius:6px; padding:6px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s; font-size:13px;">
                        <i class="fas fa-plus"></i>
                    </button>
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
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; margin-top:8px;">
                    <span style="color:#64748b;">Service Charge</span>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <label style="font-size:10px; color:#334155; display:flex; align-items:center; gap:4px;">
                            <input type="checkbox" id="serviceChargeEnabled" checked onchange="onServiceChargeInputChange()" style="width:14px; height:14px;">
                            <span>Enable</span>
                        </label>
                           <input type="number" id="serviceChargeRate" value="8" min="0" max="100" step="0.1" oninput="recalcTotal()" onchange="onServiceChargeInputChange()"
                               style="width:50px; font-size:10px; border:1px solid #e2e8f0; border-radius:5px; padding:3px 6px; outline:none; background:#f8fafc;">
                        <span style="font-size:10px; color:#64748b;">%</span>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; color:#64748b; font-size:11px; padding-top:4px;">
                    <span>Service Charge Amount</span>
                    <span id="serviceChargeDisplay" style="font-weight:600; color:#374151;">Rs. 0.00</span>
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
                    <button class="pay-method-btn" data-method="split" onclick="selectPaymentMethod('split')" style="flex:1; padding:6px 4px; font-size:10px;">
                        <i class="fas fa-sitemap" style="display:block; font-size:13px; margin-bottom:2px;"></i>Split
                    </button>
                </div>
                <!-- Cash amount input -->
                <div id="cashSection" style="display:flex; flex-direction:column; gap:4px;">
                    <div style="display:flex; gap:6px;">
                        <div style="flex:1;">
                            <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Paid</label>
                            <input type="number" id="amountPaid" placeholder="0.00" min="0" oninput="updateChange()"
                                   style="width:100%; font-size:11px; font-weight:700; border:1.5px solid #e2e8f0; border-radius:5px; padding:5px 6px; outline:none; box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor=getAmountBorderColor()">
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Change</label>
                            <div id="changeDisplay" style="font-size:12px; font-weight:700; color:#94a3b8; padding:5px 6px; background:#f8fafc; border-radius:5px; border:1px solid #e2e8f0; text-align:center;">Rs. 0.00</div>
                        </div>
                    </div>
                    <div id="amountPaidError" style="display:none; font-size:10px; font-weight:600; color:#dc2626; padding:3px 4px; background:#fef2f2; border-radius:4px; border:1px solid #fecaca;">
                        <i class="fas fa-exclamation-circle" style="margin-right:3px;"></i>
                        <span id="amountPaidErrorText">Paid amount must cover the total.</span>
                    </div>
                </div>
                <!-- Card amount display -->
                <div id="cardSection" style="display:none; gap:6px;">
                    <div style="flex:1;">
                        <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Paid</label>
                        <div id="cardPaidDisplay" style="font-size:12px; font-weight:700; color:#0f172a; padding:5px 6px; background:#f8fafc; border-radius:5px; border:1px solid #e2e8f0; text-align:center;">Rs. 0.00</div>
                    </div>
                </div>
                <!-- Split payment section -->
                <div id="splitSection" style="display:none; flex-direction:column; gap:4px;">
                    <div style="display:flex; gap:6px;">
                        <div style="flex:1;">
                            <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Cash</label>
                            <input type="number" id="splitCashAmount" placeholder="0.00" min="0" oninput="updateSplitTotal()"
                                   style="width:100%; font-size:11px; font-weight:700; border:1.5px solid #e2e8f0; border-radius:5px; padding:5px 6px; outline:none; box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Card</label>
                            <input type="number" id="splitCardAmount" placeholder="0.00" min="0" oninput="updateSplitTotal()"
                                   style="width:100%; font-size:11px; font-weight:700; border:1.5px solid #e2e8f0; border-radius:5px; padding:5px 6px; outline:none; box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <div style="flex:1;">
                            <label style="font-size:9px; font-weight:600; color:#64748b; display:block; margin-bottom:2px;">Total Paid</label>
                            <div id="splitTotalDisplay" style="font-size:12px; font-weight:700; color:#0f172a; padding:5px 6px; background:#f8fafc; border-radius:5px; border:1px solid #e2e8f0; text-align:center;">Rs. 0.00</div>
                        </div>
                    </div>
                    <div id="splitError" style="display:none; font-size:10px; font-weight:600; color:#dc2626; padding:3px 4px; background:#fef2f2; border-radius:4px; border:1px solid #fecaca;">
                        <i class="fas fa-exclamation-circle" style="margin-right:3px;"></i>
                        <span id="splitErrorText">Total paid must cover the bill amount.</span>
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
            <p style="font-size:13px; color:#64748b; margin:0 0 3px;" id="kotTableNumber">Table —</p>
            <p style="font-size:13px; color:#7c3aed; font-weight:600; margin:0;" id="kotTokenNumber" style="display:none;"></p>
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

<!-- ══════════════════════════════════════════════════
     MODAL: Token Selection (Multi-Order at One Table)
══════════════════════════════════════════════════ -->
<div id="tokenSelectModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;"><i class="fas fa-ticket-alt" style="color:#7c3aed; margin-right:6px;"></i>Table Tokens</h2>
                <p style="font-size:12px; color:#64748b; margin:4px 0 0;">Select token to load order or create new</p>
            </div>
            <button onclick="closeModal('tokenSelectModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <div id="tokenSelectList" style="display:flex; flex-direction:column; gap:10px; max-height:300px; overflow-y:auto; margin-bottom:16px;"></div>
        <button onclick="startNewTokenAtTable()" class="btn-primary" style="width:100%; padding:12px;">
            <i class="fas fa-plus" style="margin-right:6px;"></i>Create New Token
        </button>
    </div>
</div>

<!-- MODAL: Create New Customer -->
<div id="createCustomerModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;"><i class="fas fa-user-plus" style="color:#16a34a; margin-right:6px;"></i>New Customer</h2>
                <p style="font-size:12px; color:#64748b; margin:4px 0 0;">Quick add for POS orders</p>
            </div>
            <button onclick="closeModal('createCustomerModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
            <div>
                <label style="font-size:11px; font-weight:600; color:#64748b; display:block; margin-bottom:4px;">Title (Optional)</label>
                <select id="newCustomerTitle" style="width:100%; font-size:11px; border:1.5px solid #e2e8f0; border-radius:6px; padding:6px 8px; background:#fff; outline:none; box-sizing:border-box;">
                    <option value="">—</option>
                    <option value="Mr.">Mr.</option>
                    <option value="Miss">Miss</option>
                    <option value="Master">Master</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; font-weight:600; color:#64748b; display:block; margin-bottom:4px;">Name <span style="color:#dc2626;">*</span></label>
                <input type="text" id="newCustomerName" placeholder="Customer name" style="width:100%; font-size:11px; border:1.5px solid #bfdbfe; border-radius:6px; padding:6px 8px; background:#fff; outline:none; box-sizing:border-box;"
                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#bfdbfe'">
            </div>
            <div>
                <label style="font-size:11px; font-weight:600; color:#64748b; display:block; margin-bottom:4px;">Phone Number <span style="color:#dc2626;">*</span></label>
                <input type="tel" id="newCustomerPhone" placeholder="07X XXX XXXX" style="width:100%; font-size:11px; border:1.5px solid #bfdbfe; border-radius:6px; padding:6px 8px; background:#fff; outline:none; box-sizing:border-box;"
                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#bfdbfe'">
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="closeModal('createCustomerModal')" class="btn-secondary" style="flex:1; padding:10px;">Cancel</button>
            <button onclick="saveNewCustomer()" class="btn-green" style="flex:1; padding:10px;">
                <i class="fas fa-check" style="margin-right:4px;"></i>Create & Select
            </button>
        </div>
    </div>
</div>

<!-- Toast notification -->
<div id="toast"></div>

<!-- Shift Alert Modal -->
<div id="shiftAlertModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:400px; text-align:center;">
        <i class="fas fa-exclamation-triangle" style="font-size:48px; color:#dc2626; margin-bottom:16px; display:block;"></i>
        <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0 0 8px;">Shift Not Started</h2>
        <p style="font-size:14px; color:#64748b; margin:0 0 24px;">You must start your shift before making a sale.</p>
        <div style="display:flex; gap:10px;">
            <button onclick="closeModal('shiftAlertModal')" class="btn-secondary" style="flex:1;">Close</button>
            <a href="{{ route('clerk-balancings.create') }}" class="btn-primary" style="flex:1; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-play" style="margin-right:6px;"></i>Start Shift
            </a>
        </div>
    </div>
</div>

<!-- Order History Modal -->
<div id="orderHistoryModal" class="modal-overlay">
    <div class="modal-box" style="max-width:600px; max-height:90vh;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;"><i class="fas fa-history" style="color:#3b82f6; margin-right:6px;"></i>Order History</h2>
            <button onclick="closeModal('orderHistoryModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <!-- History tabs -->
        <div style="display:flex; gap:8px; margin-bottom:14px; padding-bottom:12px; border-bottom:2px solid #f1f5f9;">
            <button id="historyTabBill" onclick="switchHistoryTab('bill')" style="padding:7px 16px; border-radius:8px; font-size:12px; font-weight:700; border:2px solid #3b82f6; background:#3b82f6; color:#fff; cursor:pointer; transition:all 0.15s;">
                <i class="fas fa-receipt" style="margin-right:5px;"></i>Bill History
            </button>
            <button id="historyTabKot" onclick="switchHistoryTab('kot')" style="padding:7px 16px; border-radius:8px; font-size:12px; font-weight:700; border:2px solid #e2e8f0; background:#f8fafc; color:#64748b; cursor:pointer; transition:all 0.15s;">
                <i class="fas fa-fire-burner" style="margin-right:5px;"></i>KOT History
            </button>
        </div>
        <div id="orderHistoryList" style="display:flex; flex-direction:column; gap:12px; max-height:calc(90vh - 175px); overflow-y:auto;"></div>
        <div id="kotHistoryList" style="display:none; flex-direction:column; gap:12px; max-height:calc(90vh - 175px); overflow-y:auto;"></div>
    </div>
</div>

<!-- Order Details Modal (for viewing items and reprinting) -->
<div id="orderDetailsModal" class="modal-overlay">
    <div class="modal-box" style="max-width:500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;"><i class="fas fa-receipt" style="color:#3b82f6; margin-right:6px;"></i>Order Details</h2>
                <p style="font-size:11px; color:#64748b; margin:4px 0 0;" id="orderDetailsOrderNum">—</p>
            </div>
            <button onclick="closeModal('orderDetailsModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>

        <!-- Order Info -->
        <div style="background:#f8fafc; border-radius:10px; padding:12px; margin-bottom:16px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:12px;">
                <div>
                    <p style="color:#64748b; margin:0 0 3px; font-weight:600;">Type</p>
                    <p style="color:#0f172a; margin:0; font-weight:700;" id="orderDetailsType">—</p>
                </div>
                <div>
                    <p style="color:#64748b; margin:0 0 3px; font-weight:600;">Payment Method</p>
                    <p style="color:#0f172a; margin:0; font-weight:700; text-transform:capitalize;" id="orderDetailsPayment">—</p>
                </div>
                <div>
                    <p style="color:#64748b; margin:0 0 3px; font-weight:600;">Customer</p>
                    <p style="color:#0f172a; margin:0; font-weight:700;" id="orderDetailsCustomer">—</p>
                </div>
                <div>
                    <p style="color:#64748b; margin:0 0 3px; font-weight:600;">Date</p>
                    <p style="color:#0f172a; margin:0; font-weight:700;" id="orderDetailsDate">—</p>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div style="margin-bottom:16px;">
            <h3 style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin:0 0 10px; letter-spacing:0.05em;">Menu Items</h3>
            <div id="orderDetailsItems" style="background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; display:flex; flex-direction:column; gap:8px; padding:12px;"></div>
        </div>

        <!-- Totals -->
        <div style="background:#f8fafc; border-radius:10px; padding:12px; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:6px;">
                <span style="color:#64748b;">Subtotal</span>
                <span style="font-weight:600; color:#374151;" id="orderDetailsSubtotal">Rs. 0.00</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:6px;">
                <span style="color:#64748b;">Discount</span>
                <span style="font-weight:600; color:#ef4444;" id="orderDetailsDiscount">Rs. 0.00</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; padding-top:6px; border-top:2px solid #e2e8f0;">
                <span style="color:#0f172a;">Total</span>
                <span style="color:#dc2626;" id="orderDetailsTotal">Rs. 0.00</span>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex; gap:10px;">
            <button onclick="closeModal('orderDetailsModal')" class="btn-secondary" style="flex:1;">Close</button>
            <button onclick="reprintOrderBill()" class="btn-blue" style="flex:1;"><i class="fas fa-print" style="margin-right:4px;"></i>Reprint Bill</button>
        </div>
    </div>
</div>

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
    let currentCategoryId      = 0;
    let selectedTableForTokens = null;
    let hasOpenShift = {{ $hasOpenShift ? 'true' : 'false' }};

    // ── Shift Guard ──
    function showShiftAlert() {
        document.getElementById('shiftAlertModal').style.display = 'flex';
    }

    // ── Bootstrap ──
    async function initPos() {
        await loadTables();
        loadCategories();
        await loadProducts();
        updateHeldOrdersBadge();
        setupEventListeners();
    }

    // ═══════════════════════════════════════════
    // TABLES
    // ═══════════════════════════════════════════

    async function loadTables() {
        try {
            // Load tokens list
            const res = await fetch('{{ route("pos.tokens") }}');
            if (!res.ok) { toast('Failed to load tokens', 'error'); return; }
            allTables = await res.json();
            renderTables();
            updateTableStatusBadge();
        } catch (e) {
            console.error('Load tokens error:', e);
            toast('Error loading tokens', 'error');
        }
    }

    function updateTableStatusBadge() {
        const badge = document.getElementById('tableStatusBadge');
        if (!badge) return;
        const occupied = allTables.filter(t => t.total > 0).length;
        const total    = allTables.length;
        badge.textContent = occupied + '/' + total + ' occupied';
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

        // allTables now contains tokens directly from getTokens() API
        const filtered  = tableFilter === 'all'
            ? allTables
            : allTables.filter(t => t.section === tableFilter);

        if (!allTables || allTables.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#94a3b8; padding:32px 12px; font-size:13px;">No active tokens</p>';
            return;
        }

        // Render tokens - single column list
        container.innerHTML = allTables.map(function(token) {
            const clickFn = 'selectTokenOrder(' + token.order_id + ')';
            const isSelected = currentOrder && currentOrder.id === token.order_id;

            const customerInfo = token.customer_name ? escapeHtml(token.customer_name) : (token.table_number ? 'Table ' + token.table_number : 'Customer');

            return '<div class="table-card" onclick="' + clickFn + '" style="cursor:pointer; padding:14px 12px; text-align:left; background:linear-gradient(135deg,#f0f9ff,#e0f2fe); border:2px solid #0284c7; border-radius:10px; transition:all 0.2s; ' + (isSelected ? 'border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15);' : '') + '" onmouseover="this.style.background=\'#e0f2fe\'; this.style.borderColor=\'#0369a1\';" onmouseout="this.style.background=\'linear-gradient(135deg,#f0f9ff,#e0f2fe)\'; this.style.borderColor=\'#0284c7\';">'
                + '<div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">'
                + '<div style="flex:1;">'
                + '<div style="font-size:18px; font-weight:900; color:#0c4a6e; display:flex; align-items:center; margin-bottom:4px;"><i class="fas fa-ticket-alt" style="margin-right:6px; font-size:16px;"></i>' + escapeHtml(token.token_number) + '</div>'
                + '<div style="font-size:12px; color:#64748b; font-weight:600;">' + customerInfo + '</div>'
                + '</div>'
                + '<div style="text-align:right; flex-shrink:0;">'
                + '<div style="font-size:13px; font-weight:800; color:#0c4a6e;">Rs. ' + token.total.toFixed(2) + '</div>'
                + '</div>'
                + '</div>'
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
            if (!res.ok) { 
                toast('Failed to load order', 'error'); 
                hideLoading(); 
                return; 
            }
            
            currentOrder = await res.json();
            currentTable = allTables.find(t => t.id === currentOrder.table_id) || null;

            // Reset discount first, then render will re-apply if customer exists
            document.getElementById('discountType').value = '';
            document.getElementById('discountValue').value = '';
            const discountBadge = document.getElementById('tierDiscountBadge');
            if (discountBadge) discountBadge.style.display = 'none';

            // Collapse cards and select current
            document.querySelectorAll('.table-card.expanded').forEach(c => c.classList.remove('expanded'));
            document.querySelectorAll('.table-card.selected').forEach(c => c.classList.remove('selected'));
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
            // Cancel the order on the server to free the table
            if (currentOrder && currentOrder.id) {
                try {
                    await fetch('{{ route("pos.order.close_table", ":id") }}'.replace(':id', currentOrder.id), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                } catch (e) {
                    console.error('Close table error:', e);
                }
            }
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
            id: data.order_id, 
            order_number: data.order_number,
            items: [], 
            subtotal: 0, 
            total: 0,
            service_charge_enabled: true,
            service_charge_rate: 8,
            service_charge_amount: 0,
            discount_amount: 0, 
            live_bill_enabled: false,
            customer_name: null, 
            customer_phone: null,
            table_id: tableId,
        };

        // === IMPORTANT: Reset discount when starting fresh order ===
        document.getElementById('discountType').value = '';
        document.getElementById('discountValue').value = '';
        const discountBadge = document.getElementById('tierDiscountBadge');
        if (discountBadge) discountBadge.style.display = 'none';

        renderTableView();
        renderBill();
        await loadTables();
        hideLoading();
        toast('Table ' + table.table_number + ' opened', 'success');
    }

    function showTableSelectionForNewToken() {
        if (!hasOpenShift) {
            showShiftAlert();
            return;
        }
        const list = document.getElementById('tokenSelectList');

        // Show all tables to select from for new token
        list.innerHTML = allTables.map(table => {
            return '<button onclick="selectedTableForTokens=' + table.id + '; startNewTokenAtTable();" '
                + 'style="padding:16px; border:2px solid #0284c7; border-radius:12px; background:linear-gradient(135deg,#f0f9ff,#e0f2fe); cursor:pointer; transition:all 0.2s; width:100%; text-align:left;" '
                + 'onmouseover="this.style.borderColor=\'#0369a1\'; this.style.background=\'#e0f2fe\';" '
                + 'onmouseout="this.style.borderColor=\'#0284c7\'; this.style.background=\'linear-gradient(135deg,#f0f9ff,#e0f2fe)\';">'
                + '<div style="display:flex; justify-content:space-between; align-items:center;">'
                + '<div style="text-align:left;">'
                + '<p style="font-size:16px; font-weight:900; color:#0c4a6e; margin:0;">Table ' + table.table_number + '</p>'
                + '<p style="font-size:12px; color:#64748b; margin:4px 0 0;">' + escapeHtml(table.name) + ' • Cap: ' + table.capacity + '</p>'
                + '</div>'
                + '<span style="font-size:12px; font-weight:700; color:#0c4a6e;">➕</span>'
                + '</div>'
                + '</button>';
        }).join('');
        openModal('tokenSelectModal');
    }

    function showTokenSelectionModal(tableId) {
        const table = allTables.find(t => t.id === tableId);
        if (!table) {
            toast('Table not found', 'error');
            return;
        }

        selectedTableForTokens = tableId;
        const list = document.getElementById('tokenSelectList');

        let tokensHtml = '';

        // If table has active tokens, show them - simplified design
        if (table.active_tokens && table.active_tokens.length > 0) {
            tokensHtml = table.active_tokens.map(token => {
                return '<button onclick="selectTokenAndLoadOrder(' + token.order_id + '); event.stopPropagation();" '
                    + 'style="padding:16px; border:2px solid #7c3aed; border-radius:12px; background:#f5f3ff; cursor:pointer; transition:all 0.2s; width:100%; text-align:left;" '
                    + 'onmouseover="this.style.borderColor=\'#6d28d9\'; this.style.background=\'#ede9fe\';" '
                    + 'onmouseout="this.style.borderColor=\'#7c3aed\'; this.style.background=\'#f5f3ff\';">'
                    + '<div style="display:flex; justify-content:space-between; align-items:center;">'
                    + '<div style="text-align:left;">'
                    + '<p style="font-size:16px; font-weight:900; color:#7c3aed; margin:0;"><i class="fas fa-ticket-alt" style="margin-right:6px;"></i>' + escapeHtml(token.token_number) + '</p>'
                    + (token.customer_name ? '<p style="font-size:12px; color:#64748b; margin:4px 0 0;">' + escapeHtml(token.customer_name) + '</p>' : '')
                    + '</div>'
                    + '<span style="font-size:15px; font-weight:800; color:#7c3aed;">Rs. ' + token.total.toFixed(2) + '</span>'
                    + '</div>'
                    + '</button>';
            }).join('');
        }

        list.innerHTML = tokensHtml;
        openModal('tokenSelectModal');
    }

    function selectTokenOrder(orderId) {
        selectTokenAndLoadOrder(orderId);
    }

    async function selectTokenAndLoadOrder(orderId) {
        try {
            showLoading();
            const res = await fetch('{{ route("pos.order.show", ":id") }}'.replace(':id', orderId));
            if (!res.ok) {
                toast('Failed to load order', 'error');
                hideLoading();
                return;
            }

            currentOrder = await res.json();
            currentTable = allTables.find(t => t.id === currentOrder.table_id) || null;

            document.getElementById('discountType').value = '';
            document.getElementById('discountValue').value = '';
            document.getElementById('serviceChargeEnabled').checked = currentOrder.service_charge_enabled !== false;
            document.getElementById('serviceChargeRate').value = currentOrder.service_charge_rate ?? 8;
            const discountBadge = document.getElementById('tierDiscountBadge');
            if (discountBadge) discountBadge.style.display = 'none';

            document.querySelectorAll('.table-card.expanded').forEach(c => c.classList.remove('expanded'));
            document.querySelectorAll('.table-card.selected').forEach(c => c.classList.remove('selected'));
            if (currentTable) {
                const card = document.getElementById('tc-' + currentTable.id);
                if (card) card.classList.add('selected');
            }

            renderTableView();
            renderBill();
            closeModal('tokenSelectModal');
            hideLoading();

        } catch (e) {
            console.error('Select token error:', e);
            hideLoading();
            toast('Error loading order', 'error');
        }
    }

    async function createQuickToken() {
        if (!hasOpenShift) {
            showShiftAlert();
            return;
        }

        const res = await fetch('{{ route("pos.order.create") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_type: 'dine_in'
            })
        });

        if (!res.ok) {
            toast('Failed to create new token', 'error');
            return;
        }

        const data = await res.json();
        currentOrder = {
            id: data.order_id,
            order_number: data.order_number,
            token_number: data.token_number,
            items: [],
            subtotal: 0,
            total: 0,
            service_charge_enabled: true,
            service_charge_rate: 8,
            service_charge_amount: 0,
            discount_amount: 0,
            live_bill_enabled: false,
            customer_name: null,
            customer_phone: null,
            table_id: null,
        };

        document.getElementById('discountType').value = '';
        document.getElementById('discountValue').value = '';
        document.getElementById('serviceChargeEnabled').checked = true;
        document.getElementById('serviceChargeRate').value = 8;
        const discountBadge = document.getElementById('tierDiscountBadge');
        if (discountBadge) discountBadge.style.display = 'none';

        renderTableView();
        renderBill();
        toast('Token ' + data.token_number + ' created!', 'success');
        loadTables(); // fire and forget — update sidebar without blocking
    }

    async function startNewTokenAtTable() {
        if (!hasOpenShift) {
            showShiftAlert();
            return;
        }
        if (!selectedTableForTokens) return;
        const table = allTables.find(t => t.id === selectedTableForTokens);
        if (!table) return;

        closeModal('tokenSelectModal');
        showLoading();

        const res = await fetch('{{ route("pos.order.create") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                table_id: selectedTableForTokens,
                order_type: 'dine_in'
            })
        });

        if (!res.ok) {
            hideLoading();
            toast('Failed to create new order', 'error');
            return;
        }

        const data = await res.json();
        currentTable = table;
        currentOrder = {
            id: data.order_id,
            order_number: data.order_number,
            token_number: data.token_number,
            items: [],
            subtotal: 0,
            total: 0,
            service_charge_enabled: true,
            service_charge_rate: 8,
            service_charge_amount: 0,
            discount_amount: 0,
            live_bill_enabled: false,
            customer_name: null,
            customer_phone: null,
            table_id: selectedTableForTokens,
        };

        document.getElementById('discountType').value = '';
        document.getElementById('discountValue').value = '';
        document.getElementById('serviceChargeEnabled').checked = true;
        document.getElementById('serviceChargeRate').value = 8;
        const discountBadge = document.getElementById('tierDiscountBadge');
        if (discountBadge) discountBadge.style.display = 'none';

        renderTableView();
        renderBill();
        await loadTables();
        hideLoading();
        toast('New token created - ' + data.token_number, 'success');
    }

    async function startTakeawayOrder(forceType) {
        if (!hasOpenShift) {
            showShiftAlert();
            return;
        }
        showLoading();
        try {
            // Deselect any previously selected table
            document.querySelectorAll('.table-card.selected').forEach(function(c) { c.classList.remove('selected'); });
            currentTable = null;

            let orderType = 'takeaway';
            if (typeof forceType === 'string' && ['takeaway', 'delivery', 'vip_room'].includes(forceType)) {
                orderType = forceType;
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
                service_charge_enabled: true,
                service_charge_rate: 8,
                service_charge_amount: 0,
                discount_amount: 0,
                live_bill_enabled: false,
                customer_name: null,
                customer_phone: null,
                table_id: null,
                order_type: orderType,
                table_number: null,
                table_name: null
            };

            document.getElementById('serviceChargeEnabled').checked = true;
            document.getElementById('serviceChargeRate').value = 8;
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
            currentCategoryId = categoryId;
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

    function getActiveCategoryId() {
        const active = document.querySelector('#categoriesContainer .cat-pill.active');
        if (active && active.getAttribute('data-category')) {
            return parseInt(active.getAttribute('data-category'), 10) || 0;
        }
        return currentCategoryId || 0;
    }

    async function refreshProducts() {
        const searchInput = document.getElementById('searchInput');
        const searchValue = searchInput ? searchInput.value : '';
        await loadProducts(searchValue, getActiveCategoryId());
    }

    function applyProductStockUpdate(updatedProduct) {
        if (!updatedProduct || !allProducts.length) return;
        const index = allProducts.findIndex(function(p) { return p.id === updatedProduct.id; });
        if (index === -1) return;
        allProducts[index].quantity = updatedProduct.quantity;
        allProducts[index].is_unlimited_stock = updatedProduct.is_unlimited_stock;
        renderProducts();
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
        currentCategoryId = id;
        loadProducts(document.getElementById('searchInput').value, id);
    }

    function renderProducts() {
        const container = document.getElementById('productsContainer');
        if (allProducts.length === 0) {
            container.innerHTML = '<p style="grid-column:1/-1; text-align:center; color:#94a3b8; padding:48px 0; font-size:13px;"><i class="fas fa-search" style="font-size:28px; display:block; margin-bottom:10px;"></i>No products found</p>';
            return;
        }
        container.innerHTML = allProducts.map(function(p) {
            const outOfStock = !p.is_unlimited_stock && p.quantity <= 0;
            const stockLabel = p.is_unlimited_stock ? '∞' : p.quantity;
            const stockClass = outOfStock ? 'stock-badge out' : 'stock-badge in';
            const cardClass = outOfStock ? 'product-card product-card--disabled' : 'product-card';
            const nameArg = JSON.stringify(p.name || '');
            const clickAction = outOfStock
                ? ''
                : "onclick='addProductToOrder(" + p.id + ", " + nameArg + ", " + p.price + ")'";
            let imageHtml = '';
            if (p.image) {
                imageHtml = '<img src="/storage/' + p.image + '" alt="' + escapeHtml(p.name) + '" '
                    + 'style="width:100%; height:100%; object-fit:cover;">';
            } else {
                imageHtml = '<i class="fas fa-utensils" style="color:#dc2626; font-size:18px;"></i>';
            }

            return '<div class="' + cardClass + '" ' + clickAction + '>'
                + '<div style="height:80px; background:linear-gradient(135deg,#fef2f2,#fee2e2); border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:10px; overflow:hidden; position:relative;">'
                + imageHtml
                + '<span class="' + stockClass + '">Stock: ' + stockLabel + '</span>'
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
        if (!hasOpenShift) {
            showShiftAlert();
            return;
        }
        const product = allProducts.find(function(p) { return p.id === productId; });
        if (product && !product.is_unlimited_stock && product.quantity <= 0) {
            toast('Out of stock', 'error');
            return;
        }
        if (!currentOrder || !currentOrder.id) {
            toast('Please select or create a token first', 'error');
            return;
        }

        // Verify order is valid before adding items
        if (!currentOrder || !currentOrder.id || !Array.isArray(currentOrder.items)) {
            toast('No active order. Please create a token first.', 'error');
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
        const data = await res.json().catch(function() { return {}; });
        if (!res.ok || !data.success) {
            console.error('Add item failed:', data);
            toast(data.message || 'Failed to add item to order', 'error');
            syncOrder();
            refreshProducts();
            return;
        }
        applyProductStockUpdate(data.product);
        // Must sync after add so items get real DB ids (needed for remove/update)
        await syncOrder();
        loadTables(); // fire and forget — update sidebar total
    }

    async function syncOrder() {
        try {
            if (!currentOrder || !currentOrder.id) return;
            const res = await fetch('{{ route("pos.order.show", ":id") }}'.replace(':id', currentOrder.id));
            if (!res.ok) {
                console.error('Sync failed:', res.status);
                return;
            }
            const data = await res.json();
            currentOrder = data;
            renderBill();
            renderTableView();
        } catch (e) {
            console.error('Sync order error:', e);
        }
    }

    // Increase quantity by index (works for items with or without ID)
    async function increaseQtyByIndex(index) {
        if (!currentOrder || !currentOrder.items || !currentOrder.items[index]) return;
        const item = currentOrder.items[index];
        item.quantity++;
        item.subtotal = item.unit_price * item.quantity;
        recalcOrderTotals();
        renderBill();

        // If item has an ID, sync with server
        if (item.id) {
            const res = await fetch('{{ route("pos.item.update", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', item.id), {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ quantity: item.quantity })
            });
            const data = await res.json().catch(function() { return {}; });
            if (!res.ok || !data.success) {
                toast(data.message || 'Failed to update item', 'error');
                syncOrder();
                refreshProducts();
                return;
            }
            applyProductStockUpdate(data.product);
            loadTables(); // fire and forget
        }
    }

    // Decrease quantity by index (works for items with or without ID)
    async function decreaseQtyByIndex(index) {
        if (!currentOrder || !currentOrder.items || !currentOrder.items[index]) return;
        const item = currentOrder.items[index];
        if (item.quantity <= 1) return;
        item.quantity--;
        item.subtotal = item.unit_price * item.quantity;
        recalcOrderTotals();
        renderBill();

        // If item has an ID, sync with server
        if (item.id) {
            const res = await fetch('{{ route("pos.item.update", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', item.id), {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ quantity: item.quantity })
            });
            const data = await res.json().catch(function() { return {}; });
            if (!res.ok || !data.success) {
                toast(data.message || 'Failed to update item', 'error');
                syncOrder();
                refreshProducts();
                return;
            }
            applyProductStockUpdate(data.product);
            loadTables(); // fire and forget
        }
    }

    // Original functions kept for backward compatibility
    async function increaseQty(itemId) {
        const item = currentOrder.items.find(function(i) { return i.id === itemId; });
        if (!item) return;
        item.quantity++;
        item.subtotal = item.unit_price * item.quantity;
        recalcOrderTotals();
        renderBill();
        const res = await fetch('{{ route("pos.item.update", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', itemId), {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: item.quantity })
        });
        const data = await res.json().catch(function() { return {}; });
        if (!res.ok || !data.success) {
            toast(data.message || 'Failed to update item', 'error');
            syncOrder();
            refreshProducts();
            return;
        }
        applyProductStockUpdate(data.product);
        loadTables(); // fire and forget
    }

    async function decreaseQty(itemId) {
        const item = currentOrder.items.find(function(i) { return i.id === itemId; });
        if (!item || item.quantity <= 1) return;
        item.quantity--;
        item.subtotal = item.unit_price * item.quantity;
        recalcOrderTotals();
        renderBill();
        const res = await fetch('{{ route("pos.item.update", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', itemId), {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: item.quantity })
        });
        const data = await res.json().catch(function() { return {}; });
        if (!res.ok || !data.success) {
            toast(data.message || 'Failed to update item', 'error');
            syncOrder();
            refreshProducts();
            return;
        }
        applyProductStockUpdate(data.product);
        loadTables(); // fire and forget
    }

    // Remove item by array index (works for items with or without ID)
    async function removeItemByIndex(index) {
        if (!currentOrder || !currentOrder.items || !currentOrder.items[index]) return;
        const item = currentOrder.items[index];

        // Remove from UI immediately and capture remaining count before any async
        currentOrder.items.splice(index, 1);
        const remainingCount = currentOrder.items.length;
        recalcOrderTotals();
        renderBill();

        // If item has an ID, remove from server
        if (item.id) {
            const res = await fetch('{{ route("pos.item.remove", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', item.id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json().catch(function() { return {}; });
            if (!res.ok || !data.success) {
                toast(data.message || 'Failed to remove item', 'error');
                syncOrder();
                refreshProducts();
                return;
            }
            applyProductStockUpdate(data.product);
        }

        if (remainingCount === 0) {
            await cancelTokenSilently();
        } else {
            loadTables();
        }
    }

    async function removeItem(itemId) {
        currentOrder.items = currentOrder.items.filter(function(i) { return i.id !== itemId; });
        // Capture remaining count before any async calls
        const remainingCount = currentOrder.items.length;
        recalcOrderTotals();
        renderBill();
        const res = await fetch('{{ route("pos.item.remove", [":id", ":item"]) }}'.replace(':id', currentOrder.id).replace(':item', itemId), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json().catch(function() { return {}; });
        if (!res.ok || !data.success) {
            toast(data.message || 'Failed to remove item', 'error');
            syncOrder();
            refreshProducts();
            return;
        }
        applyProductStockUpdate(data.product);

        if (remainingCount === 0) {
            await cancelTokenSilently();
        } else {
            loadTables();
        }
    }

    async function cancelTokenSilently() {
        if (!currentOrder || !currentOrder.id) return;
        const orderId = currentOrder.id;
        try {
            await fetch('{{ route("pos.order.cancel", ":id") }}'.replace(':id', orderId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
        } catch (e) {
            console.error('Auto-cancel token error:', e);
        }
        resetOrder();
        await loadTables();
        toast('Token removed', 'success');
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

    // function renderTableView() {
    //     if (!currentTable && !currentOrder) {
    //         document.getElementById('selectedTableLabel').innerHTML =
    //             '<i class="fas fa-arrow-left" style="font-size:11px; margin-right:4px;"></i>Select a table or create takeaway order';
    //         document.getElementById('customerInfoToggle').style.display = 'none';
    //         document.getElementById('activeOrderBanner').style.display   = 'none';
    //         return;
    //     }

    //     if (!currentTable && currentOrder) {
    //         // Takeaway / Delivery / VIP Room
    //         const displayType = currentOrder.order_type ? 
    //                             currentOrder.order_type.charAt(0).toUpperCase() + currentOrder.order_type.slice(1) : 
    //                             'Takeaway';
    //         document.getElementById('selectedTableLabel').innerHTML =
    //             '🛍 <strong>' + displayType + ' Order</strong> — ' + (currentOrder.order_number || '—');
    //         document.getElementById('customerInfoToggle').style.display = 'flex';
    //         document.getElementById('activeOrderBanner').style.display   = 'flex';
    //         document.getElementById('activeOrderText').textContent = displayType + ' — adding items';
    //     } else {
    //         // Dine-in Table
    //         const sectionLabel = currentTable.section === 'vip' ? '🟣 VIP' : '🍽';
    //         document.getElementById('selectedTableLabel').innerHTML =
    //             sectionLabel + ' <strong>Table ' + currentTable.table_number + '</strong> — ' + escapeHtml(currentTable.name);
    //         document.getElementById('customerInfoToggle').style.display = 'flex';
    //         document.getElementById('activeOrderBanner').style.display   = 'flex';
    //         document.getElementById('activeOrderText').textContent = 'Adding to Table ' + currentTable.table_number;
    //     }

    //     // ==================== CUSTOMER HANDLING ====================
    //     if (currentOrder) {
    //         const hasCustomer = currentOrder.customer_id || 
    //                         currentOrder.customer_name || 
    //                         currentOrder.customer_phone;

    //         if (hasCustomer) {
    //             const tier = currentOrder.customer?.tier || 
    //                         currentOrder.customer_tier || 
    //                         currentOrder.tier || 
    //                         'New';

    //             selectCustomer(
    //                 currentOrder.customer_id,
    //                 currentOrder.customer_name || '',
    //                 currentOrder.customer_phone || '',
    //                 tier
    //             );
    //         } else {
    //             // No customer attached
    //             document.getElementById('selectedCustomerChip').style.display = 'none';
    //             document.getElementById('customerSearchInputs').style.display = 'grid';
    //             document.getElementById('customerName').value  = '';
    //             document.getElementById('customerPhone').value = '';
    //         }
    //     }

    //     // Force recalculation after render
    //     setTimeout(recalcTotal, 100);
    // }

    function renderTableView() {
        if (!currentTable && !currentOrder) {
            document.getElementById('selectedTableLabel').innerHTML =
                '<i class="fas fa-arrow-left" style="font-size:11px; margin-right:4px;"></i>Select a table or create takeaway order';
            document.getElementById('customerInfoToggle').style.display = 'none';
            document.getElementById('activeOrderBanner').style.display   = 'none';
            return;
        }

        // Table / Order Type Header
        if (!currentTable && currentOrder) {
            const displayType = currentOrder.order_type ? 
                                currentOrder.order_type.charAt(0).toUpperCase() + currentOrder.order_type.slice(1) : 
                                'Takeaway';
            document.getElementById('selectedTableLabel').innerHTML =
                '🛍 <strong>' + displayType + ' Order</strong> — ' + (currentOrder.order_number || '—');
            document.getElementById('activeOrderText').textContent = displayType + ' — adding items';
        } else if (currentTable) {
            const sectionLabel = currentTable.section === 'vip' ? '🟣 VIP' : '🍽';
            document.getElementById('selectedTableLabel').innerHTML =
                sectionLabel + ' <strong>Table ' + currentTable.table_number + '</strong> — ' + escapeHtml(currentTable.name);
            document.getElementById('activeOrderText').textContent = 'Adding to Table ' + currentTable.table_number;
        }

        document.getElementById('customerInfoToggle').style.display = 'flex';
        document.getElementById('activeOrderBanner').style.display   = 'flex';

        // ==================== RESTORE CUSTOMER (Critical Fix) ====================
        if (currentOrder) {
            const customerId   = currentOrder.customer_id || currentOrder.customer?.id;
            const customerName = currentOrder.customer_name || currentOrder.customer?.name || '';
            const customerPhone = currentOrder.customer_phone || currentOrder.customer?.phone_number || '';
            const tier         = currentOrder.customer?.tier || 
                                currentOrder.customer_tier || 
                                currentOrder.tier || 
                                'New';

            if (customerId || customerName || customerPhone) {
                selectCustomer(customerId, customerName, customerPhone, tier);
            } else {
                // No customer
                document.getElementById('selectedCustomerChip').style.display = 'none';
                document.getElementById('customerSearchInputs').style.display = 'grid';
                document.getElementById('customerName').value  = '';
                document.getElementById('customerPhone').value = '';
            }
        }

        // Recalculate discount after customer is restored
        setTimeout(() => {
            recalcTotal();
        }, 150);
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
            document.getElementById('billItems').innerHTML = currentOrder.items.map(function(item, index) {
                const noteHtml = item.kitchen_notes
                    ? '<p style="font-size:10px; color:#f59e0b; margin:2px 0 0;"><i class="fas fa-note-sticky" style="margin-right:3px;"></i>' + escapeHtml(item.kitchen_notes) + '</p>'
                    : '';

                // Allow remove button for items with ID
                const removeBtn = item.id
                    ? '<button onclick="removeItem(' + item.id + ')" style="font-size:10px; color:#ef4444; background:none; border:none; cursor:pointer; padding:0; margin-top:3px;"><i class="fas fa-trash"></i> Remove</button>'
                    : '<button onclick="removeItemByIndex(' + index + ')" style="font-size:10px; color:#ef4444; background:none; border:none; cursor:pointer; padding:0; margin-top:3px;"><i class="fas fa-trash"></i> Remove</button>';

                // Allow quantity buttons for all items (with or without ID)
                const decBtn = '<button class="qty-btn" onclick="decreaseQtyByIndex(' + index + ')">−</button>';
                const incBtn = '<button class="qty-btn" onclick="increaseQtyByIndex(' + index + ')">+</button>';

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
        const serviceCharge = calcServiceCharge(subtotal);
        const total    = Math.max(0, subtotal - discount + serviceCharge);

        document.getElementById('subtotalDisplay').textContent = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('serviceChargeDisplay').textContent = 'Rs. ' + serviceCharge.toFixed(2);
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
        const cancelBtn = document.getElementById('cancelTokenBtn');
        if (cancelBtn) {
            cancelBtn.style.display = (currentOrder && currentOrder.id && !hasItems) ? 'inline-flex' : 'none';
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
                discountBadge.innerHTML = `
                    <i class="fas fa-tag" style="font-size:9px;"></i>
                    <span>${tier} discount: ${pct}% applied</span>
                `;
                discountBadge.style.display = 'flex';
            }
        } else {
            // Clear discount if tier has 0%
            discountTypeEl.value  = '';
            discountValueEl.value = '';
            if (discountBadge) discountBadge.style.display = 'none';
        }

        recalcTotal();
    }

    function clearSelectedCustomer() {
        _selectedCustomerId = null;

        // Reset UI
        document.getElementById('selectedCustomerChip').style.display = 'none';
        document.getElementById('customerSearchInputs').style.display = 'grid';
        document.getElementById('customerName').value  = '';
        document.getElementById('customerPhone').value = '';

        // === FORCE REMOVE TIER DISCOUNT ===
        const discountTypeEl  = document.getElementById('discountType');
        const discountValueEl = document.getElementById('discountValue');
        const discountBadge   = document.getElementById('tierDiscountBadge');

        // Clear any tier-based discount
        discountTypeEl.value  = '';
        discountValueEl.value = '';

        if (discountBadge) {
            discountBadge.style.display = 'none';
        }

        // Recalculate total immediately
        recalcTotal();

        // Save to backend (customer removed)
        saveCustomerInfo();

        toast('Customer removed', 'success');
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

    function openCreateCustomerModal() {
        document.getElementById('newCustomerTitle').value = '';
        document.getElementById('newCustomerName').value = '';
        document.getElementById('newCustomerPhone').value = '';
        openModal('createCustomerModal');
    }

    async function saveNewCustomer() {
        const title = document.getElementById('newCustomerTitle').value.trim();
        const name = document.getElementById('newCustomerName').value.trim();
        const phone = document.getElementById('newCustomerPhone').value.trim();

        if (!name || !phone) {
            toast('Name and phone number are required', 'error');
            return;
        }

        try {
            showLoading();
            const res = await fetch('{{ route("pos.create.customer.quick") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    phone_number: phone,
                    title: title || null
                })
            });

            if (!res.ok) {
                hideLoading();
                toast('Failed to create customer', 'error');
                return;
            }

            const customer = await res.json();
            hideLoading();
            closeModal('createCustomerModal');

            // Auto-select the newly created customer
            selectCustomer(customer.id, customer.name, customer.phone_number, customer.tier);
            toast('Customer created and selected', 'success');
        } catch (e) {
            console.error('Create customer error:', e);
            hideLoading();
            toast('Error creating customer: ' + e.message, 'error');
        }
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
        document.getElementById('cardSection').style.display = method === 'card' ? 'flex' : 'none';
        document.getElementById('splitSection').style.display = method === 'split' ? 'flex' : 'none';
        if (method !== 'cash') document.getElementById('changeDisplay').textContent = 'Rs. 0.00';
        updateCardPaidDisplay();
        updateSplitTotal();
    }

    function calcDiscount(subtotal) {
        const type  = document.getElementById('discountType').value;
        const value = parseFloat(document.getElementById('discountValue').value) || 0;
        if (type === 'percentage') return (subtotal * value) / 100;
        if (type === 'fixed')      return value;
        return 0;
    }

    function calcServiceCharge(subtotal) {
        const enabled = document.getElementById('serviceChargeEnabled')?.checked;
        const rate = parseFloat(document.getElementById('serviceChargeRate')?.value) || 0;
        if (!enabled || rate <= 0) return 0;
        return (subtotal * rate) / 100;
    }

    async function onServiceChargeInputChange() {
        if (!currentOrder || !currentOrder.id) {
            recalcTotal();
            return;
        }

        const enabled = document.getElementById('serviceChargeEnabled')?.checked ?? true;
        const rate = parseFloat(document.getElementById('serviceChargeRate')?.value) || 0;
        currentOrder.service_charge_enabled = enabled;
        currentOrder.service_charge_rate = rate;
        currentOrder.service_charge_amount = calcServiceCharge(currentOrder.subtotal || 0);
        recalcTotal();

        try {
            const res = await fetch('{{ route("pos.order.service_charge", ":id") }}'.replace(':id', currentOrder.id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    service_charge_enabled: enabled,
                    service_charge_rate: rate,
                }),
            });
            if (res.ok) {
                const data = await res.json();
                currentOrder.service_charge_amount = data.service_charge_amount;
                currentOrder.total = data.total;
                // Keep the token list total in sync with the current order state
                const tokenIndex = allTables.findIndex(t => t.order_id === currentOrder.id);
                if (tokenIndex !== -1) {
                    allTables[tokenIndex].total = currentOrder.total;
                }
                renderTables();
            } else {
                console.error('Unable to save service charge settings', res.status);
            }
        } catch (e) {
            console.error('Service charge save error:', e);
        }
    }

    function recalcTotal() {
        if (!currentOrder) return;

        const subtotal = currentOrder.subtotal || 0;
        const discount = calcDiscount(subtotal);
        const serviceCharge = calcServiceCharge(subtotal);
        const total    = Math.max(0, subtotal - discount + serviceCharge);

        document.getElementById('subtotalDisplay').textContent = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('serviceChargeDisplay').textContent = 'Rs. ' + serviceCharge.toFixed(2);
        document.getElementById('totalDisplay').textContent    = 'Rs. ' + total.toFixed(2);

        updateChange();
        updateCardPaidDisplay();
    }

    function getTotalDue() {
        if (!currentOrder) return 0;
        const subtotal = currentOrder.subtotal || 0;
        const discount = calcDiscount(subtotal);
        const serviceCharge = calcServiceCharge(subtotal);
        return Math.max(0, subtotal - discount + serviceCharge);
    }

    function updateCardPaidDisplay() {
        if (selectedPaymentMethod !== 'card') return;
        const total = getTotalDue();
        const el = document.getElementById('cardPaidDisplay');
        if (el) el.textContent = 'Rs. ' + total.toFixed(2);
    }

    function updateSplitTotal() {
        if (selectedPaymentMethod !== 'split') return;
        const total = getTotalDue();
        const cashAmount = parseFloat(document.getElementById('splitCashAmount').value) || 0;
        const cardAmount = parseFloat(document.getElementById('splitCardAmount').value) || 0;
        const totalPaid = cashAmount + cardAmount;
        const totalDisplay = document.getElementById('splitTotalDisplay');
        const errorEl = document.getElementById('splitError');

        totalDisplay.textContent = 'Rs. ' + totalPaid.toFixed(2);

        if (totalPaid < total) {
            totalDisplay.style.color = '#dc2626';
            errorEl.style.display = 'flex';
        } else {
            totalDisplay.style.color = '#16a34a';
            errorEl.style.display = 'none';
        }
    }

    function getAmountBorderColor() {
        if (selectedPaymentMethod !== 'cash') return '#e2e8f0';
        const total = getTotalDue();
        const paid  = parseFloat(document.getElementById('amountPaid').value) || 0;
        if (paid > 0 && paid < total) return '#dc2626';
        if (paid >= total && paid > 0) return '#16a34a';
        return '#e2e8f0';
    }

    function updateChange() {
        if (selectedPaymentMethod !== 'cash') return;
        const total     = getTotalDue();
        const paid      = parseFloat(document.getElementById('amountPaid').value) || 0;
        const change    = Math.max(0, paid - total);
        const changeEl  = document.getElementById('changeDisplay');
        const errorEl   = document.getElementById('amountPaidError');
        const errorText = document.getElementById('amountPaidErrorText');
        const inputEl   = document.getElementById('amountPaid');

        // Change display
        changeEl.textContent = 'Rs. ' + change.toFixed(2);

        if (paid <= 0) {
            // Nothing entered yet — neutral state
            changeEl.style.color      = '#94a3b8';
            changeEl.style.background = '#f8fafc';
            changeEl.style.borderColor = '#e2e8f0';
            errorEl.style.display = 'none';
            inputEl.style.borderColor = '#e2e8f0';
        } else if (paid < total) {
            // Underpayment — show error
            const shortfall = (total - paid).toFixed(2);
            changeEl.textContent       = 'Rs. 0.00';
            changeEl.style.color       = '#dc2626';
            changeEl.style.background  = '#fef2f2';
            changeEl.style.borderColor = '#fecaca';
            errorText.textContent      = 'Short by Rs. ' + shortfall + ' — enter at least Rs. ' + total.toFixed(2);
            errorEl.style.display      = 'flex';
            inputEl.style.borderColor  = '#dc2626';
        } else {
            // Sufficient — show change in green
            changeEl.style.color       = '#16a34a';
            changeEl.style.background  = '#f0fdf4';
            changeEl.style.borderColor = '#bbf7d0';
            errorEl.style.display      = 'none';
            inputEl.style.borderColor  = '#16a34a';
        }
    }

    async function initiatePayment() {
        if (!currentOrder || !currentOrder.id || !currentOrder.items || !currentOrder.items.length) {
            toast('No items in order', 'error'); return;
        }
        await saveCustomerInfo();

        const total = getTotalDue();
        if (selectedPaymentMethod === 'cash') {
            const paidValue = parseFloat(document.getElementById('amountPaid').value);
            if (!Number.isFinite(paidValue) || paidValue <= 0) {
                toast('Enter the cash received amount.', 'error');
                document.getElementById('amountPaid').focus();
                return;
            }
            if (paidValue < total) {
                const shortfall = (total - paidValue).toFixed(2);
                toast('Insufficient amount — short by Rs. ' + shortfall, 'error');
                document.getElementById('amountPaid').focus();
                updateChange(); // re-trigger to show inline error
                return;
            }
        }
        if (selectedPaymentMethod === 'split') {
            const cashAmount = parseFloat(document.getElementById('splitCashAmount').value) || 0;
            const cardAmount = parseFloat(document.getElementById('splitCardAmount').value) || 0;
            const totalPaid = cashAmount + cardAmount;
            if (totalPaid <= 0) {
                toast('Enter payment amounts for split payment.', 'error');
                document.getElementById('splitCashAmount').focus();
                return;
            }
            if (totalPaid < total) {
                const shortfall = (total - totalPaid).toFixed(2);
                toast('Insufficient amount — short by Rs. ' + shortfall, 'error');
                updateSplitTotal();
                return;
            }
        }
        const amountPaid  = selectedPaymentMethod === 'cash'
            ? parseFloat(document.getElementById('amountPaid').value)
            : selectedPaymentMethod === 'split'
            ? parseFloat(document.getElementById('splitCashAmount').value) + parseFloat(document.getElementById('splitCardAmount').value)
            : total;

        const res = await fetch('{{ route("pos.order.pay", ":id") }}'.replace(':id', currentOrder.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                payment_method: selectedPaymentMethod,
                amount_paid:    amountPaid,
                discount_type:  document.getElementById('discountType').value || null,
                discount_value: parseFloat(document.getElementById('discountValue').value) || 0,
                    service_charge_enabled: document.getElementById('serviceChargeEnabled')?.checked || false,
                    service_charge_rate: parseFloat(document.getElementById('serviceChargeRate').value) || 0,
            })
        });
        if (!res.ok) {
            toast('Payment failed — server error', 'error');
            return;
        }
        const data = await res.json();
        if (data.success) {
            showPaidBill(data);
            await loadTables();
            toast('Payment received — table closed!', 'success');
        } else {
            toast(data.error || 'Payment failed', 'error');
        }
    }

    function showPaidBill(d) {
        const methodLabel   = { cash:'Cash', card:'Card', bank_transfer:'Bank Transfer', mixed:'Mixed', split:'Split Payment' };
        const locLabel      = (d.order_type && d.order_type !== 'dine_in')
            ? d.order_type.replace('_', ' ').toUpperCase()
            : 'T-' + (d.table_number || '—') + (d.table_name ? ' ' + d.table_name : '');
        const discountLabel = d.discount_amount > 0
            ? (d.discount_type === 'percentage'
                ? 'Discount (' + d.discount_value + '%)'
                : 'Discount (Fixed)')
            : null;

        const metaRows = [
            ['Order', d.order_number],
            ['Type',  locLabel],
            ['Date',  new Date().toLocaleString()],
        ];
        if (d.token_number) {
            metaRows.splice(1, 0, ['Token', d.token_number]);
        }

        const html = rcptHeader('RECEIPT')
            + rcptMeta(metaRows)
            + (d.customer_name  ? '<div class="row sm mt4"><span class="label">Customer</span><span class="value">' + escapeHtml(d.customer_name) + '</span></div>' : '')
            + (d.customer_phone ? '<div class="row sm"><span class="label">Phone</span><span class="value">' + escapeHtml(d.customer_phone) + '</span></div>' : '')
            + rcptItemHeader()
            + rcptItemRows(d.items)
            + rcptTotalsWithLabel(d.subtotal, d.discount_amount || 0, discountLabel, d.service_charge_amount || 0, d.total)
            + '<div class="row mt4"><span class="label">Paid (' + (methodLabel[d.payment_method] || d.payment_method) + ')</span><span class="value bold">Rs.' + d.amount_paid.toFixed(2) + '</span></div>'
            + (d.change_amount > 0 ? '<div class="row"><span class="label">Change</span><span class="value">Rs.' + d.change_amount.toFixed(2) + '</span></div>' : '')
            + '<div class="divider-dashed mt8"></div>'
            + '<div class="center sm mt4">Thank you for dining with us!</div>'
            + '<div class="center sm mt2">We look forward to seeing you again.</div>';

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

        const discountType  = document.getElementById('discountType').value  || null;
        const discountValue = parseFloat(document.getElementById('discountValue').value) || 0;

        const res  = await fetch('{{ route("pos.order.waiter_bill", ":id") }}'.replace(':id', currentOrder.id), {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                discount_type: discountType,
                discount_value: discountValue,
                service_charge_enabled: document.getElementById('serviceChargeEnabled')?.checked || false,
                service_charge_rate: parseFloat(document.getElementById('serviceChargeRate').value) || 0,
            }),
        });
        const data = await res.json();
        if (!data.success) { toast('Could not generate bill', 'error'); return; }

        const locLabel = data.order_type === 'dine_in'
            ? 'T-' + (data.table_number || '—') + (data.table_name ? ' ' + data.table_name : '')
            : (data.order_type || '').replace('_', ' ').toUpperCase();

        const discountLabel = data.discount_amount > 0
            ? (data.discount_type === 'percentage'
                ? 'Discount (' + data.discount_value + '%)'
                : 'Discount (Fixed)')
            : null;

        const metaRows = [
            ['Order', data.order_number],
            ['Type',  locLabel],
            ['Date',  new Date().toLocaleString()],
        ];
        if (data.token_number) {
            metaRows.splice(1, 0, ['Token', data.token_number]);
        }

        const html = rcptHeader('BILL')
            + rcptMeta(metaRows)
            + (data.customer_name  ? '<div class="row sm mt4"><span class="label">Customer</span><span class="value">' + escapeHtml(data.customer_name) + '</span></div>' : '')
            + (data.customer_phone ? '<div class="row sm"><span class="label">Phone</span><span class="value">' + escapeHtml(data.customer_phone) + '</span></div>' : '')
            + rcptItemHeader()
            + rcptItemRows(data.items)
            + rcptTotalsWithLabel(data.subtotal, data.discount_amount || 0, discountLabel, data.service_charge_amount || 0, data.total)
            + '<div class="divider-dashed mt8"></div>'
            + '<div class="center sm bold">** NOT A PAYMENT RECEIPT **</div>'
            + '<div class="center sm">Please pay at the counter</div>';

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
        if (!res.ok || !data.success) {
            toast(data.message || 'No new kitchen items to print', 'error');
            return;
        }
        document.getElementById('kotOrderNumber').textContent = 'Order #' + data.order_number;
        document.getElementById('kotTableNumber').textContent = kotLocationLabel(data);
        const tokenEl = document.getElementById('kotTokenNumber');
        if (data.token_number) {
            tokenEl.textContent = 'Token: ' + data.token_number;
            tokenEl.style.display = 'block';
        } else {
            tokenEl.style.display = 'none';
        }
        renderKotItems(data.items);
        currentKotContent = buildKotHtml(data);
        openModal('kotModal');
    }

    async function printKotForTable(orderId) {
        const res  = await fetch('{{ route("pos.order.kot", ":id") }}'.replace(':id', orderId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            toast(data.message || 'No new kitchen items to print', 'error');
            return;
        }
        document.getElementById('kotOrderNumber').textContent = 'Order #' + data.order_number;
        document.getElementById('kotTableNumber').textContent = kotLocationLabel(data);
        const tokenEl = document.getElementById('kotTokenNumber');
        if (data.token_number) {
            tokenEl.textContent = 'Token: ' + data.token_number;
            tokenEl.style.display = 'block';
        } else {
            tokenEl.style.display = 'none';
        }
        renderKotItems(data.items);
        currentKotContent = buildKotHtml(data);
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

    function kotLocationLabel(data) {
        const type = (data.order_type || '').toLowerCase();
        if (type === 'takeaway')    return 'TAKEAWAY';
        if (type === 'delivery')    return 'DELIVERY';
        if (type === 'vip_room')    return 'VIP ROOM';
        // dine_in or unknown — show table number
        return 'TABLE ' + (data.table_number || '—');
    }

    function buildKotHtml(data) {
        const itemCount = data.items.reduce(function(s, i) { return s + i.quantity; }, 0);
        const locLabel  = kotLocationLabel(data);

        const items = data.items.map(function(i) {
            return '<div class="kot-item">'
                + '<span class="kot-item-name">' + escapeHtml(i.product_name) + '</span>'
                + '<span class="kot-item-qty">x' + i.quantity + '</span>'
                + '</div>'
                + (i.kitchen_notes
                    ? '<div class="sm mb4" style="padding:2px 4px; background:#eee;">&#9658; ' + escapeHtml(i.kitchen_notes) + '</div>'
                    : '');
        }).join('');

        return '<div class="center mb8">'
            + '<div class="bold xl">KITCHEN ORDER</div>'
            + '<div class="bold" style="font-size:13px; margin-top:4px; background:#000; color:#fff; padding:3px 10px; display:inline-block;">' + locLabel + '</div>'
            + '</div>'
            + '<div class="divider-solid"></div>'
            + '<div class="row sm mt4 mb4"><span class="label">Order:</span><span class="value bold">' + data.order_number + '</span></div>'
            + (data.table_name ? '<div class="row sm mb4"><span class="label">Table:</span><span class="value bold">' + escapeHtml(data.table_name) + '</span></div>' : '')
            + (data.token_number ? '<div class="row sm mb4"><span class="label">Token:</span><span class="value bold">' + data.token_number + '</span></div>' : '')
            + '<div class="row sm mb4"><span class="label">Time:</span><span class="value">' + new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) + '</span></div>'
            + '<div class="divider-double"></div>'
            + items
            + '<div class="divider-solid mt4"></div>'
            + '<div class="center sm bold mt4">Total Items: ' + itemCount + '</div>';
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
        await loadTables();
        loadHeldOrders();
    }

    async function updateHeldOrdersBadge() {
        try {
            const res    = await fetch('{{ route("pos.held") }}');
            if (!res.ok) return;
            const orders = await res.json();
            const badge  = document.getElementById('heldCount');
            badge.textContent = orders.length;
            badge.style.background = orders.length > 0 ? '#f59e0b' : '#94a3b8';
        } catch (e) {
            console.error('Update held orders badge error:', e);
        }
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
                        + '<p style="font-size:13px; font-weight:800; color:#0f172a; margin:0;">' + o.order_number + (o.token_number ? ' <span style="font-size:11px; color:#7c3aed; margin-left:4px;">(' + o.token_number + ')</span>' : '') + '</p>'
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

        resetOrder();
        renderTableView();
        renderBill();
        toast('Order panel closed', 'success');
    }

    async function cancelToken() {
        if (!currentOrder || !currentOrder.id) return;

        const tokenLabel = currentOrder.token_number || currentOrder.order_number || 'this token';
        if (!confirm('Cancel and permanently delete ' + tokenLabel + '? This cannot be undone.')) return;

        showLoading();
        try {
            const res = await fetch('{{ route("pos.order.cancel", ":id") }}'.replace(':id', currentOrder.id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                toast(err.message || 'Failed to cancel token', 'error');
                hideLoading();
                return;
            }
            resetOrder();
            await loadTables();
            hideLoading();
            toast('Token cancelled and removed', 'success');
        } catch (e) {
            console.error('Cancel token error:', e);
            hideLoading();
            toast('Error cancelling token', 'error');
        }
    }

    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════

    function resetOrder() {
        currentOrder = null;
        currentTable = null;
        selectedPaymentMethod = 'cash';

        // === CLEAR DISCOUNT FIELDS ===
        document.getElementById('discountType').value = '';
        document.getElementById('discountValue').value = '';
        const discountBadge = document.getElementById('tierDiscountBadge');
        if (discountBadge) discountBadge.style.display = 'none';

        document.getElementById('billItems').innerHTML = '<div style="text-align:center; padding:48px 0; color:#cbd5e1;"><i class="fas fa-utensils" style="font-size:36px; margin-bottom:12px; display:block;"></i><p style="font-size:13px; margin:0;">Select a table or create takeaway order</p></div>';
        document.getElementById('selectedTableLabel').innerHTML = '<i class="fas fa-arrow-left" style="font-size:11px; margin-right:4px;"></i>Select a table or create takeaway order';
        document.getElementById('customerInfoToggle').style.display     = 'none';
        document.getElementById('customerInfoSection').style.display    = 'none';
        document.getElementById('activeOrderBanner').style.display      = 'none';
        document.getElementById('paymentSection').style.display         = 'none';
        document.getElementById('waiterPayRow').style.display           = 'none';
        document.getElementById('holdBtn').style.display                = 'none';
        
        document.getElementById('customerName').value   = '';
        document.getElementById('customerPhone').value  = '';
        _selectedCustomerId = null;
        document.getElementById('selectedCustomerChip').style.display = 'none';
        document.getElementById('customerSearchInputs').style.display = 'grid';
        
        document.getElementById('amountPaid').value     = '';
        document.getElementById('changeDisplay').textContent  = 'Rs. 0.00';
        document.getElementById('subtotalDisplay').textContent = 'Rs. 0.00';
        document.getElementById('totalDisplay').textContent    = 'Rs. 0.00';

        document.querySelectorAll('.pay-method-btn').forEach(function(b) {
            b.classList.toggle('active', b.dataset.method === 'cash');
        });
        document.getElementById('cashSection').style.display = 'flex';
        document.getElementById('cardSection').style.display = 'none';
        document.querySelectorAll('.table-card.expanded').forEach(c => c.classList.remove('expanded'));
        document.querySelectorAll('.table-card.selected').forEach(c => c.classList.remove('selected'));
        const cancelBtn = document.getElementById('cancelTokenBtn');
        if (cancelBtn) cancelBtn.style.display = 'none';
    }
    // ─── shared print CSS injected into every receipt window ───────────────
    const RECEIPT_CSS = `
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            font-weight: 900;
            color: #000;
            background: #fff;
            width: 80mm;
            padding: 4mm 3mm 6mm;
        }
        @media print {
            @page { size: 80mm auto; margin: 2mm 5mm 2mm 8mm; }
            body  { width: 100%; padding: 0.5mm 0; }
        }
        .center  { text-align: center; }
        .right   { text-align: right; }
        .bold    { font-weight: bold; }
        .lg      { font-size: 14px; }
        .xl      { font-size: 18px; }
        .sm      { font-size: 9px; }
        .mt4     { margin-top: 4px; }
        .mb4     { margin-bottom: 4px; }
        .mt8     { margin-top: 8px; }
        .mb8     { margin-bottom: 8px; }
        .divider-solid  { border-top: 1px solid #000; margin: 6px 0; }
        .divider-dashed { border-top: 1px dashed #000; margin: 5px 0; }
        .divider-double { border-top: 3px double #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; align-items: flex-start; margin: 2px 0; }
        .row .label { flex: 1; }
        .row .value { white-space: nowrap; padding-left: 8px; }
        .item-name  { flex: 1; word-break: break-word; }
        .item-qty   { width: 22px; text-align: center; flex-shrink: 0; }
        .item-amt   { width: 75px; text-align: right; flex-shrink: 0; }
        .mt2  { margin-top: 2px; }
        .mb8  { margin-bottom: 8px; }
        .kot-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed #000;
        }
        .kot-item-name { flex: 1; font-size: 14px; font-weight: 900; }
        .kot-item-qty  { font-size: 22px; font-weight: 900; white-space: nowrap; padding-left: 8px; }
    `;

    function printReceipt(html) {
        const w = window.open('', '_blank', 'width=380,height=680,toolbar=0,menubar=0,scrollbars=1');
        if (!w) { toast('Allow popups to print receipts', 'error'); return; }
        w.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print</title><style>' + RECEIPT_CSS + '</style></head><body>' + html + '</body></html>');
        w.document.close();
        w.focus();
        setTimeout(function () { w.print(); setTimeout(function () { w.close(); }, 800); }, 350);
    }

    // ─── receipt building blocks ─────────────────────────────────────────────
    function rcptHeader(title, subtitle) {
        return '<div class="center mb8">'
            + '<img src="/images/logo.png" alt="Logo" style="max-width:60mm; height:auto; margin-bottom:8px;">'
            + '<div class="bold xl">JBL FOOD CORNER </div>'
            + '<div class="sm mt4">Your favourite dining destination</div>'
            + '<div class="sm">NO 41, NAWALA ROAD , NUGEGODA</div>'
            + '<div class="divider-double mt8"></div>'
            + '<div class="bold lg mt4">' + title + '</div>'
            + (subtitle ? '<div class="sm mt2" style="letter-spacing:0.05em;">' + subtitle + '</div>' : '')
            + '</div>';
    }

    function rcptMeta(lines) {
        // lines: array of [label, value] pairs
        return lines.map(function(l) {
            return '<div class="row sm mt4"><span class="label">' + l[0] + '</span><span class="value">' + l[1] + '</span></div>';
        }).join('');
    }

    function rcptItemHeader() {
        return '<div class="divider-solid"></div>'
            + '<div style="display:flex; font-size:9px; font-weight:bold; margin:2px 0;">'
            + '<span class="item-name">ITEM</span>'
            + '<span class="item-qty">QTY</span>'
            + '<span class="item-amt">AMOUNT</span>'
            + '</div>'
            + '<div class="divider-dashed"></div>';
    }

    function rcptItemRows(items) {
        return items.map(function(i) {
            return '<div style="margin:3px 0;">'
                + '<div style="display:flex; align-items:flex-start;">'
                + '<span class="item-name bold">' + escapeHtml(i.product_name) + '</span>'
                + '<span class="item-qty">' + i.quantity + '</span>'
                + '<span class="item-amt">Rs.' + i.subtotal.toFixed(2) + '</span>'
                + '</div>'
                + '<div class="sm" style="padding-left:2px; color:#333;">'
                + i.quantity + ' x Rs.' + i.unit_price.toFixed(2)
                + (i.kitchen_notes ? ' &bull; <em>' + escapeHtml(i.kitchen_notes) + '</em>' : '')
                + '</div>'
                + '</div>';
        }).join('');
    }

    function rcptTotals(subtotal, discountAmt, total) {
        return rcptTotalsWithLabel(subtotal, discountAmt, null, 0, total);
    }

    // discountLabel: optional string shown next to "Discount", e.g. "Discount (10%)" or "Discount (Fixed)"
    function rcptTotalsWithLabel(subtotal, discountAmt, discountLabel, serviceChargeAmt, total) {
        const discRow = discountAmt > 0
            ? '<div class="row"><span class="label">' + (discountLabel || 'Discount') + '</span><span class="value">-Rs.' + discountAmt.toFixed(2) + '</span></div>'
            : '';
        const serviceRow = serviceChargeAmt > 0
            ? '<div class="row"><span class="label">Service Charge</span><span class="value">Rs.' + serviceChargeAmt.toFixed(2) + '</span></div>'
            : '';
        return '<div class="divider-solid"></div>'
            + '<div class="row mt4"><span class="label">Subtotal</span><span class="value">Rs.' + subtotal.toFixed(2) + '</span></div>'
            + discRow
            + serviceRow
            + '<div class="divider-double"></div>'
            + '<div class="row bold lg"><span class="label">TOTAL</span><span class="value">Rs.' + total.toFixed(2) + '</span></div>'
            + '<div class="divider-double"></div>';
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
                if (overlay.dataset.noClose === 'true') return;
                if (e.target === overlay) overlay.classList.remove('open');
            });
        });
    }

    window.addEventListener('load', initPos);

    // ═══════════════════════════════════════════
    // ORDER HISTORY
    // ═══════════════════════════════════════════

    let currentOrderForDetails = null;
    let _kotHistoryLoaded = false;

    async function loadOrderHistory() {
        _kotHistoryLoaded = false;
        document.getElementById('kotHistoryList').innerHTML = '';
        try {
            showLoading();
            const res = await fetch('{{ route("pos.order.history") }}');
            if (!res.ok) {
                toast('Failed to load order history', 'error');
                hideLoading();
                return;
            }
            const data = await res.json();
            renderOrderHistory(data.data || []);
            switchHistoryTab('bill');
            openModal('orderHistoryModal');
            hideLoading();
        } catch (e) {
            console.error('Load order history error:', e);
            hideLoading();
            toast('Error loading order history', 'error');
        }
    }

    function renderOrderHistory(orders) {
        const container = document.getElementById('orderHistoryList');
        if (!orders || orders.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#94a3b8; padding:32px 12px; font-size:13px;">No completed orders</p>';
            return;
        }

        container.innerHTML = orders.map(order => {
            const printedDate = new Date(order.printed_at).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            return '<div style="padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; cursor:pointer; transition:all 0.2s; background:#fff;" onclick="viewOrderDetails(' + order.id + ')" onmouseover="this.style.borderColor=\'#3b82f6\'; this.style.boxShadow=\'0 4px 12px rgba(59,130,246,0.15)\';" onmouseout="this.style.borderColor=\'#e2e8f0\'; this.style.boxShadow=\'none\';">'
                + '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">'
                + '<div>'
                + '<p style="font-size:13px; font-weight:700; color:#0f172a; margin:0;">' + escapeHtml(order.order_number) + '</p>'
                + '<p style="font-size:11px; color:#64748b; margin:2px 0 0;">' + printedDate + '</p>'
                + '</div>'
                + '<span style="font-size:13px; font-weight:800; color:#dc2626;">Rs. ' + order.total.toFixed(2) + '</span>'
                + '</div>'
                + '<div style="display:flex; gap:8px; align-items:center; font-size:11px;">'
                + (order.customer_name ? '<span style="background:#eff6ff; color:#1d4ed8; padding:2px 8px; border-radius:4px; font-weight:600;">' + escapeHtml(order.customer_name) + '</span>' : '')
                + '<span style="background:#f0fdf4; color:#166534; padding:2px 8px; border-radius:4px; font-weight:600; text-transform:capitalize;">' + (order.payment_method || 'cash').replace('_', ' ') + '</span>'
                + '<span style="background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:4px; font-weight:600;">' + order.items.length + ' items</span>'
                + '</div>'
                + '</div>';
        }).join('');
    }

    function switchHistoryTab(tab) {
        const billBtn  = document.getElementById('historyTabBill');
        const kotBtn   = document.getElementById('historyTabKot');
        const billList = document.getElementById('orderHistoryList');
        const kotList  = document.getElementById('kotHistoryList');

        if (tab === 'bill') {
            billBtn.style.background  = '#3b82f6'; billBtn.style.color  = '#fff'; billBtn.style.borderColor  = '#3b82f6';
            kotBtn.style.background   = '#f8fafc'; kotBtn.style.color   = '#64748b'; kotBtn.style.borderColor = '#e2e8f0';
            billList.style.display = 'flex';
            kotList.style.display  = 'none';
        } else {
            kotBtn.style.background   = '#ea580c'; kotBtn.style.color   = '#fff'; kotBtn.style.borderColor   = '#ea580c';
            billBtn.style.background  = '#f8fafc'; billBtn.style.color  = '#64748b'; billBtn.style.borderColor = '#e2e8f0';
            kotList.style.display  = 'flex';
            billList.style.display = 'none';
            if (!_kotHistoryLoaded) {
                loadKotHistory();
            }
        }
    }

    async function loadKotHistory() {
        const container = document.getElementById('kotHistoryList');
        container.innerHTML = '<p style="text-align:center; color:#94a3b8; padding:32px 12px; font-size:13px;">Loading KOT history…</p>';
        try {
            const res = await fetch('{{ route("pos.kot.history") }}');
            if (!res.ok) {
                container.innerHTML = '<p style="text-align:center; color:#ef4444; padding:32px 12px; font-size:13px;">Failed to load KOT history</p>';
                return;
            }
            const data = await res.json();
            _kotHistoryLoaded = true;
            renderKotHistory(data.data || []);
        } catch (e) {
            console.error('Load KOT history error:', e);
            container.innerHTML = '<p style="text-align:center; color:#ef4444; padding:32px 12px; font-size:13px;">Error loading KOT history</p>';
        }
    }

    function renderKotHistory(orders) {
        const container = document.getElementById('kotHistoryList');
        if (!orders || orders.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#94a3b8; padding:32px 12px; font-size:13px;">No KOT history found</p>';
            return;
        }

        container.innerHTML = orders.map(function(order) {
            const kotDate = order.kot_printed_at ? new Date(order.kot_printed_at).toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
            }) : '—';

            const tableNum  = order.table ? order.table.table_number : null;
            const locLabel  = (order.order_type && order.order_type !== 'dine_in')
                ? order.order_type.replace('_', ' ')
                : 'Table ' + (tableNum || '—');
            const itemCount = order.items ? order.items.length : 0;

            return '<div style="padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; cursor:pointer; transition:all 0.2s; background:#fff;" onclick="reprintKotFromHistory(' + order.id + ')" onmouseover="this.style.borderColor=\'#ea580c\'; this.style.boxShadow=\'0 4px 12px rgba(234,88,12,0.15)\';" onmouseout="this.style.borderColor=\'#e2e8f0\'; this.style.boxShadow=\'none\';">'
                + '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">'
                + '<div>'
                + '<p style="font-size:13px; font-weight:700; color:#0f172a; margin:0;">' + escapeHtml(order.order_number) + '</p>'
                + '<p style="font-size:11px; color:#64748b; margin:2px 0 0;">' + kotDate + '</p>'
                + '</div>'
                + '<span style="font-size:13px; font-weight:800; color:#ea580c;">' + itemCount + ' item' + (itemCount !== 1 ? 's' : '') + '</span>'
                + '</div>'
                + '<div style="display:flex; gap:8px; align-items:center; font-size:11px; flex-wrap:wrap;">'
                + (order.token_number ? '<span style="background:#fff7ed; color:#c2410c; padding:2px 8px; border-radius:4px; font-weight:600;">' + escapeHtml(order.token_number) + '</span>' : '')
                + '<span style="background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:4px; font-weight:600; text-transform:capitalize;">' + escapeHtml(locLabel) + '</span>'
                + (order.customer_name ? '<span style="background:#eff6ff; color:#1d4ed8; padding:2px 8px; border-radius:4px; font-weight:600;">' + escapeHtml(order.customer_name) + '</span>' : '')
                + '</div>'
                + '</div>';
        }).join('');
    }

    async function reprintKotFromHistory(orderId) {
        closeModal('orderHistoryModal');
        showLoading();
        try {
            const res = await fetch('{{ route("pos.order.kot.reprint", ":id") }}'.replace(':id', orderId));
            if (!res.ok) {
                toast('Failed to load KOT data', 'error');
                hideLoading();
                openModal('orderHistoryModal');
                return;
            }
            const data = await res.json();
            if (!data.success) {
                toast(data.message || 'Failed to load KOT', 'error');
                hideLoading();
                openModal('orderHistoryModal');
                return;
            }

            document.getElementById('kotOrderNumber').textContent = 'Order #' + data.order_number;
            if (data.table_name) {
                document.getElementById('kotTableNumber').textContent = 'Table ' + data.table_number + ' — ' + data.table_name;
            } else if (data.order_type && data.order_type !== 'dine_in') {
                document.getElementById('kotTableNumber').textContent = data.order_type.replace('_', ' ').toUpperCase();
            } else {
                document.getElementById('kotTableNumber').textContent = 'Table ' + (data.table_number || '—');
            }

            const tokenEl = document.getElementById('kotTokenNumber');
            if (data.token_number) {
                tokenEl.textContent = 'Token: ' + data.token_number;
                tokenEl.style.display = 'block';
            } else {
                tokenEl.style.display = 'none';
            }

            renderKotItems(data.items);
            currentKotContent = buildKotHtml(data);
            hideLoading();
            openModal('kotModal');
        } catch (e) {
            console.error('Reprint KOT error:', e);
            hideLoading();
            toast('Error loading KOT', 'error');
            openModal('orderHistoryModal');
        }
    }

    async function viewOrderDetails(orderId) {
        try {
            showLoading();
            const res = await fetch('{{ route("pos.order.show", ":id") }}'.replace(':id', orderId));
            if (!res.ok) {
                toast('Failed to load order details', 'error');
                hideLoading();
                return;
            }
            const order = await res.json();
            currentOrderForDetails = order;
            renderOrderDetails(order);
            closeModal('orderHistoryModal');
            openModal('orderDetailsModal');
            hideLoading();
        } catch (e) {
            console.error('View order details error:', e);
            hideLoading();
            toast('Error loading order details', 'error');
        }
    }

    function renderOrderDetails(order) {
        document.getElementById('orderDetailsOrderNum').textContent = order.order_number;
        document.getElementById('orderDetailsType').textContent = (order.order_type || 'dine_in').charAt(0).toUpperCase() + (order.order_type || 'dine_in').slice(1).replace('_', ' ');
        document.getElementById('orderDetailsPayment').textContent = (order.payment_method || 'cash').replace('_', ' ');
        document.getElementById('orderDetailsCustomer').textContent = order.customer_name || '—';
        document.getElementById('orderDetailsDate').textContent = new Date(order.printed_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Render items
        const itemsContainer = document.getElementById('orderDetailsItems');
        itemsContainer.innerHTML = (order.items || []).map(item => {
            return '<div style="padding:8px 0; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:start;">'
                + '<div style="flex:1;">'
                + '<p style="font-size:12px; font-weight:700; color:#0f172a; margin:0;">' + escapeHtml(item.product_name) + '</p>'
                + '<p style="font-size:10px; color:#94a3b8; margin:2px 0 0;">' + item.quantity + ' × Rs. ' + item.unit_price.toFixed(2) + '</p>'
                + (item.kitchen_notes ? '<p style="font-size:10px; color:#f59e0b; margin:2px 0 0;"><i class="fas fa-note-sticky" style="margin-right:3px;"></i>' + escapeHtml(item.kitchen_notes) + '</p>' : '')
                + '</div>'
                + '<span style="font-size:12px; font-weight:700; color:#0f172a; min-width:70px; text-align:right;">Rs. ' + item.subtotal.toFixed(2) + '</span>'
                + '</div>';
        }).join('');

        // Render totals
        document.getElementById('orderDetailsSubtotal').textContent = 'Rs. ' + order.subtotal.toFixed(2);
        document.getElementById('orderDetailsDiscount').textContent = 'Rs. ' + order.discount_amount.toFixed(2);
        document.getElementById('orderDetailsTotal').textContent = 'Rs. ' + order.total.toFixed(2);
    }

    async function reprintOrderBill() {
        if (!currentOrderForDetails) {
            toast('No order selected', 'error');
            return;
        }

        try {
            showLoading();
            const res = await fetch('{{ route("pos.order.bill.reprint", ":id") }}'.replace(':id', currentOrderForDetails.id));
            if (!res.ok) {
                toast('Failed to load bill for reprint', 'error');
                hideLoading();
                return;
            }
            const billData = await res.json();
            if (!billData.success) {
                toast(billData.message || 'Failed to reprint bill', 'error');
                hideLoading();
                return;
            }

            // Generate bill content
            generateAndPrintBill(billData);
            hideLoading();
        } catch (e) {
            console.error('Reprint bill error:', e);
            hideLoading();
            toast('Error reprinting bill', 'error');
        }
    }

    function generateAndPrintBill(d) {
        const methodLabel = { cash: 'Cash', card: 'Card', bank_transfer: 'Bank Transfer', mixed: 'Mixed', split: 'Split Payment' };
        const locLabel = (d.order_type && d.order_type !== 'dine_in')
            ? d.order_type.replace('_', ' ').toUpperCase()
            : 'T-' + (d.table_number || '—') + (d.table_name ? ' ' + d.table_name : '');

        const metaRows = [
            ['Order', escapeHtml(d.order_number)],
            ['Type',  locLabel],
            ['Date',  d.printed_at ? new Date(d.printed_at).toLocaleString() : new Date().toLocaleString()],
        ];
        if (d.token_number) {
            metaRows.splice(1, 0, ['Token', escapeHtml(d.token_number)]);
        }

        const html = rcptHeader('RECEIPT', 'Re-Print Receipt')
            + rcptMeta(metaRows)
            + (d.customer_name  ? '<div class="row sm mt4"><span class="label">Customer</span><span class="value">' + escapeHtml(d.customer_name) + '</span></div>' : '')
            + (d.customer_phone ? '<div class="row sm"><span class="label">Phone</span><span class="value">' + escapeHtml(d.customer_phone) + '</span></div>' : '')
            + rcptItemHeader()
            + rcptItemRows(d.items)
            + rcptTotalsWithLabel(d.subtotal, d.discount_amount || 0, null, d.service_charge_amount || 0, d.total)
            + '<div class="row mt4"><span class="label">Paid (' + (methodLabel[d.payment_method] || d.payment_method) + ')</span><span class="value bold">Rs.' + d.amount_paid.toFixed(2) + '</span></div>'
            + (d.change_amount > 0 ? '<div class="row"><span class="label">Change</span><span class="value">Rs.' + d.change_amount.toFixed(2) + '</span></div>' : '')
            + '<div class="divider-dashed mt8"></div>'
            + '<div class="center sm mt4">Thank you for dining with us!</div>'
            + '<div class="center sm mt2">We look forward to seeing you again.</div>';

        currentBillContent = html;
        document.getElementById('billContent').innerHTML = html;
        openModal('finalBillModal');
    }

</script>

<script>
    document.addEventListener('focusin', function (event) {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (target.type !== 'number' && !target.dataset.clearZero) return;
        if (target.dataset.clearedZero === 'true') return;
        const value = (target.value || '').trim();
        if (value === '') return;
        if (!Number.isFinite(Number(value))) return;
        if (Number(value) !== 0) return;
        target.value = '';
        target.dataset.clearedZero = 'true';
    });
</script>
</body>
</html>
