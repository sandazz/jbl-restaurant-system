@extends('layouts.app')

@section('title', 'Product Recipe')

@section('content')
<div>
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('inventory.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Recipe</h1>
            <p class="text-gray-500 mt-1 text-sm">Raw materials consumed per portion of <span class="font-semibold text-gray-700">{{ $product->name }}</span>.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg max-w-3xl">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg max-w-3xl">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Recipe not saved:</strong>
            <ul class="list-disc list-inside mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($rawMaterials->isEmpty())
        <div class="max-w-3xl bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            No raw materials found. <a href="{{ route('raw-materials.create') }}" class="underline font-semibold">Add a raw material</a> first.
        </div>
    @endif

    <form action="{{ route('products.recipe.update', $product) }}" method="POST" class="max-w-3xl mx-auto space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-flask text-purple-500 mr-2"></i>Ingredients
                </h2>
                <button type="button" id="add-row" class="text-sm font-semibold text-red-600 hover:text-red-700">
                    <i class="fas fa-plus mr-1"></i>Add Ingredient
                </button>
            </div>

            <div id="rows" class="p-6 space-y-3">
                @forelse($product->ingredients as $index => $ingredient)
                    <div class="recipe-row flex items-center gap-3">
                        <select name="ingredients[{{ $index }}][inventory_id]" data-field="inventory_id" required
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 bg-white">
                            <option value="">— Select raw material —</option>
                            @foreach($rawMaterials as $rawMaterial)
                                <option value="{{ $rawMaterial->id }}" data-unit="{{ $rawMaterial->unit }}" {{ $ingredient->id == $rawMaterial->id ? 'selected' : '' }}>
                                    {{ $rawMaterial->name }} ({{ $rawMaterial->unit }})
                                </option>
                            @endforeach
                        </select>
                        <input type="number" step="0.001" min="0.001" name="ingredients[{{ $index }}][quantity_required]" data-field="quantity_required"
                            value="{{ $ingredient->pivot->quantity_required }}" required placeholder="Qty per portion"
                            class="w-40 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500">
                        <button type="button" class="remove-row text-gray-400 hover:text-red-600 transition-colors p-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @empty
                @endforelse
            </div>

            @if($product->ingredients->isEmpty())
                <div id="empty-state" class="text-center py-10 text-gray-400 text-sm">
                    <i class="fas fa-flask text-3xl mb-3 block text-gray-300"></i>
                    No ingredients added yet. Click "Add Ingredient" to build the recipe.
                </div>
            @endif
        </div>

        <div class="flex gap-3 pb-8">
            <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-8 py-2.5 rounded-xl font-semibold transition-colors flex items-center gap-2 text-sm shadow-sm">
                <i class="fas fa-save"></i> Save Recipe
            </button>
            <a href="{{ route('inventory.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-2.5 rounded-xl font-semibold transition-colors flex items-center gap-2 text-sm">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rowsContainer = document.getElementById('rows');
    const addRowBtn = document.getElementById('add-row');
    const emptyState = document.getElementById('empty-state');
    const rawMaterialOptions = @json($rawMaterials->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'unit' => $m->unit]));

    function buildOptions(selectedId = '') {
        let html = '<option value="">— Select raw material —</option>';
        rawMaterialOptions.forEach(m => {
            const selected = String(m.id) === String(selectedId) ? 'selected' : '';
            html += `<option value="${m.id}" data-unit="${m.unit}" ${selected}>${m.name} (${m.unit})</option>`;
        });
        return html;
    }

    function addRow(selectedId = '') {
        if (emptyState) emptyState.remove();
        const row = document.createElement('div');
        row.className = 'recipe-row flex items-center gap-3';
        row.innerHTML = `
            <select data-field="inventory_id" required
                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 bg-white">
                ${buildOptions(selectedId)}
            </select>
            <input type="number" step="0.001" min="0.001" data-field="quantity_required"
                required placeholder="Qty per portion"
                class="w-40 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500">
            <button type="button" class="remove-row text-gray-400 hover:text-red-600 transition-colors p-2">
                <i class="fas fa-trash"></i>
            </button>
        `;
        rowsContainer.appendChild(row);
    }

    addRowBtn.addEventListener('click', () => addRow());

    rowsContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-row');
        if (btn) {
            btn.closest('.recipe-row').remove();
        }
    });

    if (rawMaterialOptions.length && rowsContainer.children.length === 0) {
        addRow(rawMaterialOptions[0].id);
    }

    // Reindex every row's field names right before submit so each row's
    // inventory_id/quantity_required always land in the same array slot,
    // regardless of add/remove order (unindexed ingredients[][x] names
    // split across slots unreliably once rows are added/removed via JS).
    rowsContainer.closest('form').addEventListener('submit', () => {
        rowsContainer.querySelectorAll('.recipe-row').forEach((row, i) => {
            row.querySelectorAll('[data-field]').forEach(field => {
                field.name = `ingredients[${i}][${field.dataset.field}]`;
            });
        });
    });
});
</script>
@endsection
