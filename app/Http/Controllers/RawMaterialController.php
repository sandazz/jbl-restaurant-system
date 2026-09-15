<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Supplier;
use App\Services\InventoryDeductionService;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    public function index()
    {
        $items   = Inventory::with('supplier')->latest()->paginate(10);
        $modules = auth()->user()->role->modules()->get();

        return view('modules.raw-materials-list', compact('items', 'modules'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $modules   = auth()->user()->role->modules()->get();

        return view('modules.raw-materials-create', compact('suppliers', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'sku'             => 'nullable|string|max:100|unique:inventories,sku',
            'unit'            => 'required|string|max:20',
            'current_balance' => 'required|numeric|min:0',
            'reorder_level'   => 'required|numeric|min:0',
            'cost_per_unit'   => 'required|numeric|min:0',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'notes'           => 'nullable|string',
            'status'          => 'required|in:active,inactive',
        ]);

        Inventory::create($validated);

        return redirect()->route('raw-materials.index')
            ->with('success', 'Raw material created successfully.');
    }

    public function show(Inventory $rawMaterial)
    {
        $rawMaterial->load('supplier', 'menuItems');
        $movements = $rawMaterial->movements()->with('user')->latest()->paginate(20);
        $modules = auth()->user()->role->modules()->get();

        return view('modules.raw-materials-show', compact('rawMaterial', 'movements', 'modules'));
    }

    public function edit(Inventory $rawMaterial)
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $modules   = auth()->user()->role->modules()->get();

        return view('modules.raw-materials-edit', compact('rawMaterial', 'suppliers', 'modules'));
    }

    public function update(Request $request, Inventory $rawMaterial)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'sku'             => 'nullable|string|max:100|unique:inventories,sku,' . $rawMaterial->id,
            'unit'            => 'required|string|max:20',
            'current_balance' => 'required|numeric|min:0',
            'reorder_level'   => 'required|numeric|min:0',
            'cost_per_unit'   => 'required|numeric|min:0',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'notes'           => 'nullable|string',
            'status'          => 'required|in:active,inactive',
        ]);

        $rawMaterial->update($validated);

        return redirect()->route('raw-materials.index')
            ->with('success', 'Raw material updated successfully.');
    }

    public function destroy(Inventory $rawMaterial)
    {
        $rawMaterial->delete();

        return redirect()->route('raw-materials.index')
            ->with('success', 'Raw material deleted.');
    }

    public function restock(Request $request, Inventory $rawMaterial, InventoryDeductionService $service)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500',
        ]);

        $service->restock($rawMaterial, $validated['quantity'], reason: 'purchase', notes: $validated['notes'] ?? null);

        return redirect()->route('raw-materials.show', $rawMaterial)
            ->with('success', 'Stock added successfully.');
    }
}
