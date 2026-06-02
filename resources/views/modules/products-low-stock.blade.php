@extends('layouts.app')

@section('title', 'Low Stock Alert')

@section('content')
<div>

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Low Stock Alert</h1>
                <p class="text-gray-600 mt-1">Products at or below their low stock threshold</p>
            </div>
        </div>
        <a href="{{ route('products.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold transition-colors text-sm">
            <i class="fas fa-plus mr-2"></i>Add Product
        </a>
    </div>

    {{-- Summary banner --}}
    @if($products->isEmpty())
        <div class="bg-green-50 border border-green-200 rounded-xl px-6 py-8 text-center">
            <i class="fas fa-check-circle text-green-500 text-4xl mb-3 block"></i>
            <h3 class="text-lg font-semibold text-green-800 mb-1">All stock levels are healthy</h3>
            <p class="text-green-700 text-sm">No products are currently below their low stock limit.</p>
        </div>
    @else
        <div class="mb-6 bg-orange-50 border border-orange-200 rounded-xl px-5 py-4 flex items-center gap-4">
            <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-orange-500"></i>
            </div>
            <div>
                <p class="font-semibold text-orange-900">{{ $products->count() }} product{{ $products->count() !== 1 ? 's' : '' }} need restocking</p>
                <p class="text-sm text-orange-700">These items are at or below their set low stock limit. Consider restocking soon.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Product</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Category</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Current Stock</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Low Stock Limit</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Deficit</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Supplier</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($products as $product)
                    @php
                        $deficit   = $product->low_stock_limit - $product->quantity;
                        $isCritical = $product->quantity == 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $isCritical ? 'bg-red-50' : '' }}">

                        {{-- Product --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                         class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
                                         alt="{{ $product->name }}">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-box text-gray-400 text-sm"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->name }}</p>
                                    @if($product->product_code)
                                        <p class="text-xs text-gray-400">{{ $product->product_code }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $product->category?->name ?? '—' }}
                        </td>

                        {{-- Current Stock --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-bold
                                {{ $isCritical ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $product->quantity }}
                            </span>
                        </td>

                        {{-- Low Stock Limit --}}
                        <td class="px-6 py-4 text-center text-sm text-gray-600 font-medium">
                            {{ $product->low_stock_limit }}
                        </td>

                        {{-- Deficit --}}
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-semibold {{ $isCritical ? 'text-red-600' : 'text-orange-600' }}">
                                -{{ $deficit }}
                            </span>
                        </td>

                        {{-- Supplier --}}
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $product->supplierRecord?->name ?? ($product->supplier ?? '—') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('products.edit', $product) }}"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('stock.adjustments.create') }}?product_id={{ $product->id }}"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-800 transition-colors">
                                    <i class="fas fa-plus-circle"></i> Restock
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
