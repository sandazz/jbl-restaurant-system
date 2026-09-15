@extends('layouts.app')

@section('title', 'Raw Materials')

@section('content')
    <div>
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Raw Materials</h1>
                <p class="text-gray-600 mt-2">Manage raw materials used in recipes (ingredients &amp; packaging)</p>
            </div>
            <a href="{{ route('raw-materials.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Raw Material
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">SKU</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Unit</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Balance</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Reorder Level</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Cost/Unit</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Supplier</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50 transition-colors {{ $item->isLowStock() ? 'bg-red-50' : '' }}">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <a href="{{ route('raw-materials.show', $item) }}" class="hover:text-red-600">{{ $item->name }}</a>
                                        @if($item->isLowStock())
                                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Low Stock</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->sku ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->unit }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($item->current_balance, 3) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ number_format($item->reorder_level, 3) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ number_format($item->cost_per_unit, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->supplier->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('raw-materials.show', $item) }}" class="text-gray-600 hover:text-gray-900 text-sm font-semibold">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                            <a href="{{ route('raw-materials.edit', $item) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </a>
                                            <form action="{{ route('raw-materials.destroy', $item) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="showDeleteConfirm(this.closest('form'))" class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $items->links('pagination::tailwind') }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-boxes text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No raw materials yet</h3>
                    <p class="text-gray-600 mb-4">Start by adding your first raw material (ingredient or packaging item)</p>
                    <a href="{{ route('raw-materials.create') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Raw Material
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
