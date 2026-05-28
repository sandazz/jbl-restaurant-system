<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::paginate(10);
        $modules = auth()->user()->role->modules()->get();
        return view('modules.customers-list', [
            'customers' => $customers,
            'modules' => $modules,
        ]);
    }

    public function create()
    {
        $modules = auth()->user()->role->modules()->get();
        return view('modules.customers-create', ['modules' => $modules]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        Customer::create($validated);
        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    public function show(Customer $customer)
    {
        $modules = auth()->user()->role->modules()->get();
        return view('modules.customers-show', [
            'customer' => $customer,
            'modules' => $modules,
        ]);
    }

    public function edit(Customer $customer)
    {
        $modules = auth()->user()->role->modules()->get();
        return view('modules.customers-edit', [
            'customer' => $customer,
            'modules' => $modules,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }
}
