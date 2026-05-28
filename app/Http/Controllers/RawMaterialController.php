<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    public function index()
    {
        $items   = Inventory::with('supplier')->latest()->paginate(50);
        $modules = auth()->user()->role->modules()->get();

        return view('modules.raw-materials.index', compact('items', 'modules'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $modules   = auth()->user()->role->modules()->get();

        return view('modules.raw-materials.create', compact('suppliers', 'modules'));
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
        $modules = auth()->user()->role->modules()->get();

        return view('modules.raw-materials.show', compact('rawMaterial', 'modules'));
    }

    public function edit(Inventory $rawMaterial)
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $modules   = auth()->user()->role->modules()->get();

        return view('modules.raw-materials.edit', compact('rawMaterial', 'suppliers', 'modules'));
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
}
