@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
    <div>
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-gray-500 mt-1 text-sm">Product details and recipe</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('products.edit', $product) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit Product
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
                <p class="text-sm text-gray-600 mb-1">Selling Price</p>
                <p class="text-2xl font-bold text-gray-900">LKR {{ number_format($product->selling_price ?? $product->price, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-600 mb-1">Stock</p>
                @if($product->is_unlimited_stock)
                    <p class="text-2xl font-bold text-purple-700">Unlimited</p>
                @else
                    <p class="text-2xl font-bold {{ $product->isLowStock() ? 'text-orange-600' : 'text-gray-900' }}">{{ $product->quantity }}</p>
                @endif
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-600 mb-1">Status</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($product->status) }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Details</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-600">Category</dt>
                    <dd class="text-gray-900 font-medium">{{ $product->category->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Supplier</dt>
                    <dd class="text-gray-900 font-medium">{{ $product->supplierRecord->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Product Code</dt>
                    <dd class="text-gray-900 font-medium">{{ $product->product_code ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Barcode</dt>
                    <dd class="text-gray-900 font-medium">{{ $product->barcode ?? 'N/A' }}</dd>
                </div>
                @if($product->description)
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-600">Description</dt>
                        <dd class="text-gray-900">{{ $product->description }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900"><i class="fas fa-flask text-purple-500 mr-2"></i>Recipe</h2>
                <a href="{{ route('products.recipe', $product) }}" class="text-sm font-semibold text-red-600 hover:text-red-700">
                    <i class="fas fa-edit mr-1"></i>Edit Recipe
                </a>
            </div>
            @if($product->ingredients->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Raw Material</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Quantity per Portion</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Current Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($product->ingredients as $ingredient)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <a href="{{ route('raw-materials.show', $ingredient) }}" class="hover:text-red-600">{{ $ingredient->name }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($ingredient->pivot->quantity_required, 3) }} {{ $ingredient->unit }}</td>
                                    <td class="px-6 py-4 text-sm text-right {{ $ingredient->isLowStock() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                        {{ number_format($ingredient->current_balance, 3) }} {{ $ingredient->unit }}
                                        @if($ingredient->isLowStock())
                                            <i class="fas fa-exclamation-triangle text-red-500 ml-1" title="Low stock"></i>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-flask text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No recipe defined yet</h3>
                    <p class="text-gray-600 mb-4">Add raw materials to track ingredient usage and auto-deduct stock on sale.</p>
                    <a href="{{ route('products.recipe', $product) }}" class="inline-block bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Recipe
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
