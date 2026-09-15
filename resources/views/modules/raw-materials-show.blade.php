@extends('layouts.app')

@section('title', 'Raw Material Details')

@section('content')
    <div>
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">{{ $rawMaterial->name }}</h1>
                <p class="text-gray-600 mt-2">Raw material details and usage</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('raw-materials.edit', $rawMaterial) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('raw-materials.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-3 rounded-lg font-semibold transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-600 mb-1">Current Balance</p>
                <p class="text-2xl font-bold {{ $rawMaterial->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">
                    {{ number_format($rawMaterial->current_balance, 3) }} {{ $rawMaterial->unit }}
                </p>
                @if($rawMaterial->isLowStock())
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Low Stock</span>
                @endif
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-600 mb-1">Reorder Level</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($rawMaterial->reorder_level, 3) }} {{ $rawMaterial->unit }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-600 mb-1">Cost per Unit</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($rawMaterial->cost_per_unit, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-truck-loading text-green-500 mr-2"></i>Add Stock (Restock)</h2>
            <form action="{{ route('raw-materials.restock', $rawMaterial) }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label for="quantity" class="block text-sm font-semibold text-gray-900 mb-2">Quantity to Add ({{ $rawMaterial->unit }}) <span class="text-red-600">*</span></label>
                    <input type="number" step="0.001" min="0.001" name="quantity" id="quantity" required
                        class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('quantity') ? 'border-red-600' : '' }}"
                        placeholder="e.g. 10">
                    @error('quantity')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex-1 min-w-64">
                    <label for="notes" class="block text-sm font-semibold text-gray-900 mb-2">Notes</label>
                    <input type="text" name="notes" id="notes"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="e.g. Purchased from supplier, invoice #123">
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Stock
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Details</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-600">SKU</dt>
                    <dd class="text-gray-900 font-medium">{{ $rawMaterial->sku ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Unit</dt>
                    <dd class="text-gray-900 font-medium">{{ $rawMaterial->unit }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Supplier</dt>
                    <dd class="text-gray-900 font-medium">{{ $rawMaterial->supplier->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Status</dt>
                    <dd>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $rawMaterial->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($rawMaterial->status) }}
                        </span>
                    </dd>
                </div>
                @if($rawMaterial->notes)
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-600">Notes</dt>
                        <dd class="text-gray-900">{{ $rawMaterial->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Used in Recipes</h2>
            </div>
            @if($rawMaterial->menuItems->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Product</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Quantity Required per Portion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($rawMaterial->menuItems as $product)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($product->pivot->quantity_required, 3) }} {{ $rawMaterial->unit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-flask text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Not used in any recipe yet</h3>
                    <p class="text-gray-600">Add this raw material to a product's recipe to link it here.</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Stock Movement History</h2>
            </div>
            @if($movements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Quantity</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Balance After</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Reason</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Source</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">By</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($movements as $movement)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $movement->change_type === 'increase' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="fas {{ $movement->change_type === 'increase' ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>{{ ucfirst($movement->change_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($movement->quantity, 3) }} {{ $rawMaterial->unit }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ number_format($movement->balance_after, 3) }} {{ $rawMaterial->unit }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($movement->reason ?? 'N/A') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($movement->source) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->user->name ?? 'System' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->notes ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $movements->links('pagination::tailwind') }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-history text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No stock movements yet</h3>
                    <p class="text-gray-600">Restocks and sale deductions will appear here.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
