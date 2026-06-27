@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div>
    <!-- Page header -->
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Reports</h1>
            <p class="text-gray-600 mt-2">Business analytics and sales overview</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm text-gray-500 font-medium">
                <i class="fas fa-clock mr-1"></i>As of {{ now()->format('d M Y, H:i') }}
            </span>
            <div class="flex gap-2">
                <a href="{{ route('reports.export.sales') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                    <i class="fas fa-download"></i> Sales PDF
                </a>
                <a href="{{ route('reports.export.products') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition text-sm font-medium">
                    <i class="fas fa-download"></i> Products PDF
                </a>
                <a href="{{ route('reports.export.combined') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                    <i class="fas fa-download"></i> Complete PDF
                </a>
            </div>
        </div>
    </div>

    <!-- ── SUMMARY CARDS (6 cards) ── -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">

        <!-- Total Revenue -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Revenue</p>
            <p class="text-xl font-bold text-gray-900">LKR {{ number_format($totalRevenue, 2) }}</p>
            <div class="mt-2 w-8 h-1 rounded-full bg-red-500"></div>
        </div>

        <!-- Today's Sales -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Today's Sales</p>
            <p class="text-xl font-bold text-gray-900">LKR {{ number_format($todaySales, 2) }}</p>
            <div class="mt-2 w-8 h-1 rounded-full bg-amber-400"></div>
        </div>

        <!-- This Month -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">This Month</p>
            <p class="text-xl font-bold text-gray-900">LKR {{ number_format($monthRevenue, 2) }}</p>
            <div class="mt-2 w-8 h-1 rounded-full bg-teal-400"></div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Completed Orders</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totalOrders) }}</p>
            <div class="mt-2 w-8 h-1 rounded-full bg-blue-400"></div>
        </div>

        <!-- Average Order Value -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Avg. Order Value</p>
            <p class="text-xl font-bold text-gray-900">LKR {{ number_format($avgOrderValue, 2) }}</p>
            <div class="mt-2 w-8 h-1 rounded-full bg-purple-400"></div>
        </div>

        <!-- Top Selling Product -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Top Product</p>
            <p class="text-base font-bold text-gray-900 truncate" title="{{ $topProduct }}">{{ $topProduct }}</p>
            <div class="mt-2 w-8 h-1 rounded-full bg-green-400"></div>
        </div>

    </div>

    <!-- ── REVENUE CHART (last 7 days) ── -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-chart-bar text-red-500 mr-2"></i>Revenue — Last 7 Days
        </h2>
        <div style="position:relative; height:260px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- ── TWO-COLUMN LOWER SECTION (Recent Sales + Payment Breakdown) ── -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">

        <!-- Recent Sales Table (2/3) -->
        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-receipt text-red-500 mr-2"></i>Recent Sales
                </h2>
                <span class="text-xs text-gray-400">Last 20 completed orders</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Order #</th>
                            <th class="px-4 py-3 text-left">Table</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Payment</th>
                            <th class="px-4 py-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentSales as $sale)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $sale->order_number }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $sale->table?->name ?? ($sale->table?->table_number ? 'T'.$sale->table->table_number : '—') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $sale->customer_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                LKR {{ number_format($sale->total, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $pmColors = [
                                        'cash'          => 'bg-green-100 text-green-700',
                                        'card'          => 'bg-blue-100 text-blue-700',
                                        'bank_transfer' => 'bg-purple-100 text-purple-700',
                                        'mixed'         => 'bg-orange-100 text-orange-700',
                                    ];
                                    $pmColor = $pmColors[$sale->payment_method] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $pmColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? '—')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-400 text-xs whitespace-nowrap">
                                {{ $sale->created_at->format('d M, H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No completed orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Methods Breakdown (1/3) -->
        <div class="xl:col-span-1 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-credit-card text-red-500 mr-2"></i>Payment Methods
                </h2>
            </div>
            <div class="p-6 space-y-4">
                @forelse($paymentBreakdown as $pm)
                @php
                    $pmIcons = [
                        'cash'          => 'fa-money-bill-wave text-green-500',
                        'card'          => 'fa-credit-card text-blue-500',
                        'bank_transfer' => 'fa-university text-purple-500',
                        'mixed'         => 'fa-shuffle text-orange-500',
                    ];
                    $icon = $pmIcons[$pm->payment_method] ?? 'fa-circle-question text-gray-400';
                    $pct  = $totalOrders > 0 ? round(($pm->order_count / $totalOrders) * 100) : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <i class="fas {{ $icon }}"></i>
                            {{ ucfirst(str_replace('_', ' ', $pm->payment_method)) }}
                        </span>
                        <span class="text-sm font-bold text-gray-900">{{ $pm->order_count }} orders</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-gray-100 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400 w-10 text-right">{{ $pct }}%</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">LKR {{ number_format($pm->total_revenue, 2) }}</p>
                </div>
                @empty
                <p class="text-center text-gray-400 py-6">No payment data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ── FULL SALES TABLE WITH DATE FILTER ── -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-table text-red-500 mr-2"></i>Sales Transactions
                </h2>
                @if($dateFrom || $dateTo || $search)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="font-semibold text-gray-700">{{ number_format($filteredCount) }} orders</span>
                        <span class="text-gray-400">|</span>
                        <span class="font-bold text-gray-900">LKR {{ number_format($filteredTotal, 2) }}</span>
                        <a href="{{ route('reports.index') }}" class="text-xs text-red-600 hover:underline font-medium">Clear filters</a>
                    </div>
                @endif
            </div>
            <!-- Filter bar -->
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none">
                </div>
                <div>
                    <label for="search" class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Order #, token, customer…"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-52 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none">
                </div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if($dateFrom || $dateTo || $search)
                    <a href="{{ route('reports.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Order #</th>
                        <th class="px-4 py-3 text-left">Token</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Table / Type</th>
                        <th class="px-4 py-3 text-left">Items</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-right">Discount</th>
                        <th class="px-4 py-3 text-right">Service Chg</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Payment</th>
                        <th class="px-4 py-3 text-right">Date & Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($allSales as $sale)
                    @php
                        $pmColors = [
                            'cash'          => 'bg-green-100 text-green-700',
                            'card'          => 'bg-blue-100 text-blue-700',
                            'bank_transfer' => 'bg-purple-100 text-purple-700',
                            'split'         => 'bg-orange-100 text-orange-700',
                            'mixed'         => 'bg-orange-100 text-orange-700',
                        ];
                        $pmColor = $pmColors[$sale->payment_method] ?? 'bg-gray-100 text-gray-600';
                        $tableLabel = $sale->table
                            ? 'Table ' . $sale->table->table_number
                            : ucfirst(str_replace('_', ' ', $sale->order_type ?? 'Takeaway'));
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700 whitespace-nowrap">{{ $sale->order_number }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $sale->token_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $sale->customer_name ?? '—' }}
                            @if($sale->customer_phone)
                                <div class="text-xs text-gray-400">{{ $sale->customer_phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $tableLabel }}</td>
                        <td class="px-4 py-3">
                            @if($sale->items->count())
                                <div class="text-xs text-gray-600 space-y-0.5">
                                    @foreach($sale->items->take(3) as $item)
                                        <div>{{ $item->product_name }} <span class="text-gray-400">×{{ $item->quantity }}</span></div>
                                    @endforeach
                                    @if($sale->items->count() > 3)
                                        <div class="text-gray-400">+{{ $sale->items->count() - 3 }} more</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600 whitespace-nowrap">LKR {{ number_format($sale->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($sale->discount_amount > 0)
                                <span class="text-red-500 font-medium">− LKR {{ number_format($sale->discount_amount, 2) }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($sale->service_charge_amount > 0)
                                <span class="text-gray-600">LKR {{ number_format($sale->service_charge_amount, 2) }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900 whitespace-nowrap">LKR {{ number_format($sale->total, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $pmColor }}">
                                {{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? '—')) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-400 whitespace-nowrap">
                            {{ $sale->created_at->format('d M Y') }}<br>
                            <span class="text-gray-300">{{ $sale->created_at->format('H:i') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-receipt text-3xl mb-3 block text-gray-200"></i>
                            No sales found{{ ($dateFrom || $dateTo || $search) ? ' for the selected filters' : '' }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($allSales->count())
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="8" class="px-4 py-3 text-sm font-semibold text-gray-600 text-right">
                            Page total ({{ $allSales->count() }} rows):
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900 whitespace-nowrap">
                            LKR {{ number_format($allSales->sum('total'), 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Pagination -->
        @if($allSales->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between flex-wrap gap-3">
            <p class="text-sm text-gray-500">
                Showing {{ $allSales->firstItem() }}–{{ $allSales->lastItem() }} of {{ $allSales->total() }} orders
            </p>
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($allSales->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg">← Prev</span>
                @else
                    <a href="{{ $allSales->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">← Prev</a>
                @endif

                {{-- Page numbers --}}
                @foreach($allSales->getUrlRange(max(1, $allSales->currentPage() - 2), min($allSales->lastPage(), $allSales->currentPage() + 2)) as $page => $url)
                    @if($page == $allSales->currentPage())
                        <span class="px-3 py-1.5 text-sm font-bold text-white bg-red-600 border border-red-600 rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($allSales->hasMorePages())
                    <a href="{{ $allSales->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Next →</a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg">Next →</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- ── TOP PRODUCTS TABLE ── -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">
                <i class="fas fa-trophy text-amber-400 mr-2"></i>Top Selling Products
            </h2>
            <span class="text-xs text-gray-400">By quantity sold</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Product</th>
                        <th class="px-6 py-3 text-left">Category</th>
                        <th class="px-6 py-3 text-right">Qty Sold</th>
                        <th class="px-6 py-3 text-right">Total Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topProducts as $i => $prod)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-gray-400 font-semibold">
                            @if($i === 0) <i class="fas fa-medal text-amber-400"></i>
                            @elseif($i === 1) <i class="fas fa-medal text-gray-400"></i>
                            @elseif($i === 2) <i class="fas fa-medal text-orange-400"></i>
                            @else {{ $i + 1 }}
                            @endif
                        </td>
                        <td class="px-6 py-3 font-semibold text-gray-900">{{ $prod->product_name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $prod->category_name }}</td>
                        <td class="px-6 py-3 text-right font-bold text-gray-900">{{ number_format($prod->total_qty) }}</td>
                        <td class="px-6 py-3 text-right font-semibold text-gray-900">
                            LKR {{ number_format($prod->total_revenue, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No sales data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels  = {!! $chartLabels !!};
    const data    = {!! $chartData !!};

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (LKR)',
                data: data,
                backgroundColor: 'rgba(220, 38, 38, 0.15)',
                borderColor: '#dc2626',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' LKR ' + ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        callback: v => 'LKR ' + v.toLocaleString()
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>
@endsection
