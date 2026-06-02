<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClerkBalancingRequest;
use App\Models\ClerkBalancing;
use App\Services\ShiftBalancingService;
use Illuminate\Http\Request;

class ClerkBalancingController extends Controller
{
    public function __construct(private readonly ShiftBalancingService $balancing) {}

    /** GET /clerk-balancings */
    public function index()
    {
        $records = ClerkBalancing::with('user')
            ->latest()
            ->paginate(30);

        $modules = auth()->user()->role->modules()->get();

        return view('modules.clerk-balancings.index', compact('records', 'modules'));
    }

    /** GET /clerk-balancings/create — open shift form + denomination entry */
    public function create()
    {
        $openShift = ClerkBalancing::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        $modules = auth()->user()->role->modules()->get();

        return view('modules.clerk-balancings.create', compact('openShift', 'modules'));
    }

    /**
     * POST /clerk-balancings
     * Opens a new shift for the currently authenticated cashier.
     */
    public function store(Request $request)
    {
        $existing = ClerkBalancing::where('user_id', auth()->id())
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return back()->withErrors(['shift' => 'You already have an open shift. Please close it first.']);
        }

        $validated = $request->validate([
            'opening_amount' => 'nullable|numeric|min:0',
        ]);

        $shift = $this->balancing->openShift(auth()->id(), $validated['opening_amount'] ?? 0);

        return redirect()
            ->route('clerk-balancings.show', $shift)
            ->with('success', 'Shift opened successfully.');
    }

    /** GET /clerk-balancings/{balancing} */
    public function show(ClerkBalancing $clerkBalancing)
    {
        $clerkBalancing->load('user');
        $modules = auth()->user()->role->modules()->get();

        return view('modules.clerk-balancings.show', compact('clerkBalancing', 'modules'));
    }

    /**
     * POST /clerk-balancings/{balancing}/close
     * Accepts denomination counts, computes totals & variance, closes the shift.
     */
    public function closeShift(StoreClerkBalancingRequest $request, ClerkBalancing $clerkBalancing)
    {
        if (!$clerkBalancing->isOpen()) {
            return back()->withErrors(['shift' => 'This shift is already closed.']);
        }

        $closed = $this->balancing->closeShift(
            $clerkBalancing,
            $request->only([
                'note_5000', 'note_2000', 'note_1000', 'note_500', 'note_200',
                'note_100', 'note_50', 'note_20', 'note_10',
                'coin_5', 'coin_2', 'coin_1', 'coin_50c',
            ]),
            $request->notes
        );

        return redirect()
            ->route('clerk-balancings.show', $closed)
            ->with('success', "Shift closed. Variance: {$closed->variance_type} of {$closed->variance}.");
    }
}
