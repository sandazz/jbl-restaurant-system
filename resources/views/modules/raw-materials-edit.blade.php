@extends('layouts.app')

@section('title', 'Edit Raw Material')

@section('content')
    <div>
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Edit Raw Material</h1>
            <p class="text-gray-600 mt-2">Update raw material information</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 max-w-3xl">
            <form action="{{ route('raw-materials.update', $rawMaterial) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $rawMaterial->name) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('name') ? 'border-red-600' : '' }}">
                        @error('name')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-semibold text-gray-900 mb-2">SKU</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $rawMaterial->sku) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('sku') ? 'border-red-600' : '' }}">
                        @error('sku')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="unit" class="block text-sm font-semibold text-gray-900 mb-2">Unit <span class="text-red-600">*</span></label>
                        <input type="text" name="unit" id="unit" value="{{ old('unit', $rawMaterial->unit) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('unit') ? 'border-red-600' : '' }}">
                        @error('unit')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_balance" class="block text-sm font-semibold text-gray-900 mb-2">Current Balance <span class="text-red-600">*</span></label>
                        <input type="number" step="0.001" min="0" name="current_balance" id="current_balance" value="{{ old('current_balance', $rawMaterial->current_balance) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('current_balance') ? 'border-red-600' : '' }}">
                        @error('current_balance')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reorder_level" class="block text-sm font-semibold text-gray-900 mb-2">Reorder Level <span class="text-red-600">*</span></label>
                        <input type="number" step="0.001" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', $rawMaterial->reorder_level) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('reorder_level') ? 'border-red-600' : '' }}">
                        @error('reorder_level')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cost_per_unit" class="block text-sm font-semibold text-gray-900 mb-2">Cost per Unit <span class="text-red-600">*</span></label>
                        <input type="number" step="0.01" min="0" name="cost_per_unit" id="cost_per_unit" value="{{ old('cost_per_unit', $rawMaterial->cost_per_unit) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('cost_per_unit') ? 'border-red-600' : '' }}">
                        @error('cost_per_unit')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="supplier_id" class="block text-sm font-semibold text-gray-900 mb-2">Supplier</label>
                        <select name="supplier_id" id="supplier_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('supplier_id') ? 'border-red-600' : '' }}">
                            <option value="">Select supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $rawMaterial->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-900 mb-2">Status <span class="text-red-600">*</span></label>
                        <select name="status" id="status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('status') ? 'border-red-600' : '' }}">
                            <option value="active" {{ old('status', $rawMaterial->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $rawMaterial->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-900 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ old('notes', $rawMaterial->notes) }}</textarea>
                    @error('notes')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Raw Material
                    </button>
                    <a href="{{ route('raw-materials.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
