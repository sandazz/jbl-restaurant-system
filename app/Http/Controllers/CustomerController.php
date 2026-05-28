<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TierDiscount;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /** GET /customers/search?q=... — POS autocomplete */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $customers = Customer::where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone_number', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'title', 'phone_number', 'tier']);

        return response()->json($customers);
    }

    public function index()
    {
        $customers = Customer::paginate(10);
        $modules   = auth()->user()->role->modules()->get();

        return view('modules.customers-list', compact('customers', 'modules'));
    }

    public function create()
    {
        $tiers   = TierDiscount::forDropdown();
        $modules = auth()->user()->role->modules()->get();

        return view('modules.customers-create', compact('tiers', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                    => 'nullable|in:Mr.,Miss,Master',
            'name'                     => 'required|string|max:255',
            'phone_number'             => 'required|string|max:20',
            'address'                  => 'nullable|string|max:500',
            'status'                   => 'required|in:active,inactive',
            'tier'                     => 'required|string|exists:tier_discounts,tier',
            'slmc_registration_number' => 'nullable|string|max:100',
            'print_tier_on_receipt'    => 'boolean',
        ]);

        $validated['print_tier_on_receipt'] = $request->boolean('print_tier_on_receipt');

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $modules = auth()->user()->role->modules()->get();

        return view('modules.customers-show', compact('customer', 'modules'));
    }

    public function edit(Customer $customer)
    {
        $tiers   = TierDiscount::forDropdown();
        $modules = auth()->user()->role->modules()->get();

        return view('modules.customers-edit', compact('customer', 'tiers', 'modules'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'title'                    => 'nullable|in:Mr.,Miss,Master',
            'name'                     => 'required|string|max:255',
            'phone_number'             => 'required|string|max:20',
            'address'                  => 'nullable|string|max:500',
            'status'                   => 'required|in:active,inactive',
            'tier'                     => 'required|string|exists:tier_discounts,tier',
            'slmc_registration_number' => 'nullable|string|max:100',
            'print_tier_on_receipt'    => 'boolean',
        ]);

        $validated['print_tier_on_receipt'] = $request->boolean('print_tier_on_receipt');

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
