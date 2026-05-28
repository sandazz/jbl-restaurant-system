<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /** GET /audit-logs */
    public function index(Request $request)
    {
        $query = AuditLog::with(['order', 'cashier', 'authorizingManager'])
            ->latest();

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs    = $query->paginate(50);
        $modules = auth()->user()->role->modules()->get();

        return view('modules.audit-logs.index', compact('logs', 'modules'));
    }

    /**
     * GET /audit-logs/eod
     *
     * Aggregated end-of-day summary for the administrator.
     */
    public function eodReport(Request $request)
    {
        $date = $request->filled('date')
            ? \Carbon\Carbon::parse($request->date)
            : today();

        $logs = AuditLog::with(['order', 'cashier', 'authorizingManager'])
            ->whereDate('created_at', $date)
            ->get();

        $summary = [
            'date'           => $date->toDateString(),
            'total_actions'  => $logs->count(),
            'by_action_type' => $logs->groupBy('action_type')->map->count(),
            'by_cashier'     => $logs->groupBy('cashier.name')->map->count(),
            'voids'          => $logs->where('action_type', 'void')->count(),
            'discounts'      => $logs->where('action_type', 'discount')->count(),
            'overrides'      => $logs->where('action_type', 'override')->count(),
        ];

        $modules = auth()->user()->role->modules()->get();

        return view('modules.audit-logs.eod', compact('logs', 'summary', 'date', 'modules'));
    }
}
