<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    public function index()
    {
        $modules = auth()->user()->role->modules()->get();

        // ── Summary cards ──────────────────────────────────────────
        $totalRevenue  = Order::where('status', 'completed')->sum('total');
        $todaySales    = Order::where('status', 'completed')
                              ->whereDate('created_at', Carbon::today())->sum('total');
        $monthRevenue  = Order::where('status', 'completed')
                              ->whereYear('created_at',  Carbon::now()->year)
                              ->whereMonth('created_at', Carbon::now()->month)->sum('total');
        $totalOrders          = Order::where('status', 'completed')->count();
        $avgOrderValue        = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
        $totalServiceCharges  = Order::where('status', 'completed')->sum('service_charge_amount');

        // Top selling product (by quantity sold)
        $topProductRow = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'))
                                  ->groupBy('product_name')
                                  ->orderByDesc('total_qty')
                                  ->first();
        $topProduct = $topProductRow?->product_name ?? 'N/A';

        // ── Revenue last 7 days (for Chart.js) ──────────────────────
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            $revenue = Order::where('status', 'completed')
                            ->whereDate('created_at', $date)
                            ->sum('total');
            return [
                'label'   => $date->format('D d'),   // e.g. "Mon 26"
                'revenue' => (float) $revenue,
            ];
        });
        $chartLabels  = $last7Days->pluck('label')->toJson();
        $chartData    = $last7Days->pluck('revenue')->toJson();

        // ── Recent sales table (last 20 completed orders) ───────────
        $recentSales = Order::where('status', 'completed')
                            ->with('table')
                            ->latest()
                            ->limit(20)
                            ->get();

        // ── Top products table ──────────────────────────────────────
        $topProducts = OrderItem::select(
                           'product_name',
                           DB::raw('SUM(quantity) as total_qty'),
                           DB::raw('SUM(subtotal) as total_revenue'),
                           DB::raw('MAX(product_id) as product_id')
                       )
                       ->groupBy('product_name')
                       ->orderByDesc('total_qty')
                       ->limit(10)
                       ->get()
                       ->map(function ($row) {
                           // Attach category name if product still exists
                           $product = Product::with('category')->find($row->product_id);
                           $row->category_name = $product?->category?->name ?? '—';
                           return $row;
                       });

        // ── Payment method breakdown ────────────────────────────────
        $paymentBreakdown = Order::where('status', 'completed')
                                 ->whereNotNull('payment_method')
                                 ->select('payment_method',
                                          DB::raw('COUNT(*) as order_count'),
                                          DB::raw('SUM(total) as total_revenue'))
                                 ->groupBy('payment_method')
                                 ->get();

        return view('modules.reports', compact(
            'modules',
            'totalRevenue', 'todaySales', 'monthRevenue',
            'totalOrders', 'avgOrderValue', 'topProduct',
            'totalServiceCharges',
            'chartLabels', 'chartData',
            'recentSales', 'topProducts', 'paymentBreakdown'
        ));
    }

    public function exportSalesPdf()
    {
        $totalRevenue  = Order::where('status', 'completed')->sum('total');
        $todaySales    = Order::where('status', 'completed')
                              ->whereDate('created_at', Carbon::today())->sum('total');
        $monthRevenue  = Order::where('status', 'completed')
                              ->whereYear('created_at',  Carbon::now()->year)
                              ->whereMonth('created_at', Carbon::now()->month)->sum('total');
        $totalOrders   = Order::where('status', 'completed')->count();
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        $recentSales = Order::where('status', 'completed')
                            ->with('table')
                            ->latest()
                            ->limit(100)
                            ->get();

        $paymentBreakdown = Order::where('status', 'completed')
                                 ->whereNotNull('payment_method')
                                 ->select('payment_method',
                                          DB::raw('COUNT(*) as order_count'),
                                          DB::raw('SUM(total) as total_revenue'))
                                 ->groupBy('payment_method')
                                 ->get();

        $pdf = Pdf::loadView('reports.sales-pdf', [
            'totalRevenue' => $totalRevenue,
            'todaySales' => $todaySales,
            'monthRevenue' => $monthRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'recentSales' => $recentSales,
            'paymentBreakdown' => $paymentBreakdown,
            'generatedAt' => now()->format('d M Y, H:i')
        ])->setPaper('a4', 'landscape');

        return $pdf->download('sales-report-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportProductsPdf()
    {
        $topProducts = OrderItem::select(
                           'product_name',
                           DB::raw('SUM(quantity) as total_qty'),
                           DB::raw('SUM(subtotal) as total_revenue'),
                           DB::raw('MAX(product_id) as product_id')
                       )
                       ->groupBy('product_name')
                       ->orderByDesc('total_qty')
                       ->get()
                       ->map(function ($row) {
                           $product = Product::with('category')->find($row->product_id);
                           $row->category_name = $product?->category?->name ?? '—';
                           return $row;
                       });

        $totalRevenue = Order::where('status', 'completed')->sum('total');

        $pdf = Pdf::loadView('reports.products-pdf', [
            'topProducts' => $topProducts,
            'totalRevenue' => $totalRevenue,
            'generatedAt' => now()->format('d M Y, H:i')
        ])->setPaper('a4', 'landscape');

        return $pdf->download('products-report-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function exportCombinedPdf()
    {
        $totalRevenue  = Order::where('status', 'completed')->sum('total');
        $todaySales    = Order::where('status', 'completed')
                              ->whereDate('created_at', Carbon::today())->sum('total');
        $monthRevenue  = Order::where('status', 'completed')
                              ->whereYear('created_at',  Carbon::now()->year)
                              ->whereMonth('created_at', Carbon::now()->month)->sum('total');
        $totalOrders   = Order::where('status', 'completed')->count();
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        $recentSales = Order::where('status', 'completed')
                            ->with('table')
                            ->latest()
                            ->limit(50)
                            ->get();

        $topProducts = OrderItem::select(
                           'product_name',
                           DB::raw('SUM(quantity) as total_qty'),
                           DB::raw('SUM(subtotal) as total_revenue'),
                           DB::raw('MAX(product_id) as product_id')
                       )
                       ->groupBy('product_name')
                       ->orderByDesc('total_qty')
                       ->limit(15)
                       ->get()
                       ->map(function ($row) {
                           $product = Product::with('category')->find($row->product_id);
                           $row->category_name = $product?->category?->name ?? '—';
                           return $row;
                       });

        $paymentBreakdown = Order::where('status', 'completed')
                                 ->whereNotNull('payment_method')
                                 ->select('payment_method',
                                          DB::raw('COUNT(*) as order_count'),
                                          DB::raw('SUM(total) as total_revenue'))
                                 ->groupBy('payment_method')
                                 ->get();

        $pdf = Pdf::loadView('reports.combined-pdf', [
            'totalRevenue' => $totalRevenue,
            'todaySales' => $todaySales,
            'monthRevenue' => $monthRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'paymentBreakdown' => $paymentBreakdown,
            'generatedAt' => now()->format('d M Y, H:i')
        ])->setPaper('a4', 'landscape');

        return $pdf->download('complete-report-' . now()->format('Y-m-d-His') . '.pdf');
    }
}
