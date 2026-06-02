@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div>

    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('inventory.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
            <p class="text-gray-500 mt-1 text-sm">Update details for <span class="font-semibold text-gray-700">{{ $product->name }}</span>.</p>
        </div>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="max-w-3xl mx-auto space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-info-circle text-red-500 mr-2"></i>Basic Information
                </h2>
            </div>
            <div class="p-6 space-y-5">

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}"
                        placeholder="e.g. Grilled Chicken, Pasta Carbonara…">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
                        <select name="category_id" id="category_id"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 bg-white">
                            <option value="">— Select category —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="supplier_id" class="block text-sm font-semibold text-gray-700 mb-1.5">Supplier</label>
                        <select name="supplier_id" id="supplier_id"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 bg-white">
                            <option value="">— Select supplier —</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label for="product_code" class="block text-sm font-semibold text-gray-700 mb-1.5">Product Code <span class="text-gray-400 font-normal">(SKU)</span></label>
                        <input type="text" name="product_code" id="product_code" value="{{ old('product_code', $product->product_code) }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500"
                            placeholder="e.g. PRD-001">
                        @error('product_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="barcode" class="block text-sm font-semibold text-gray-700 mb-1.5">Barcode</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500"
                            placeholder="Scan or type barcode">
                        @error('barcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 resize-none"
                        placeholder="Short description…">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-tag text-green-500 mr-2"></i>Pricing
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <label for="selling_price" class="block text-sm font-semibold text-gray-700 mb-1.5">Selling Price</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm font-medium pointer-events-none">Rs.</span>
                            <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', $product->selling_price) }}" step="0.01" min="0"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500" placeholder="0.00">
                        </div>
                        @error('selling_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="cost_price" class="block text-sm font-semibold text-gray-700 mb-1.5">Cost Price</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm font-medium pointer-events-none">Rs.</span>
                            <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500" placeholder="0.00">
                        </div>
                        @error('cost_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="discount" class="block text-sm font-semibold text-gray-700 mb-1.5">Discount</label>
                        <div class="relative">
                            <input type="number" name="discount" id="discount" value="{{ old('discount', $product->discount) }}" step="0.01" min="0" max="100"
                                class="w-full pl-4 pr-9 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500" placeholder="0">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 text-sm pointer-events-none">%</span>
                        </div>
                        @error('discount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock Management --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-boxes text-blue-500 mr-2"></i>Stock Management
                </h2>
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <span class="text-sm text-gray-600">Unlimited stock</span>
                    <div class="relative">
                        <input type="checkbox" name="is_unlimited_stock" id="is_unlimited_stock" value="1"
                            {{ old('is_unlimited_stock', $product->is_unlimited_stock) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-10 h-5 bg-gray-300 peer-checked:bg-red-500 rounded-full transition-colors duration-200"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>
            <div id="stock-fields" class="p-6">
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-1.5">Current Stock <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $product->quantity) }}" min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 {{ $errors->has('quantity') ? 'border-red-400 bg-red-50' : '' }}"
                            placeholder="0">
                        @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="low_stock_limit" class="block text-sm font-semibold text-gray-700 mb-1.5">Low Stock Alert Limit</label>
                        <input type="number" name="low_stock_limit" id="low_stock_limit" value="{{ old('low_stock_limit', $product->low_stock_limit) }}" min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500"
                            placeholder="e.g. 5">
                        <p class="text-xs text-gray-400 mt-1.5"><i class="fas fa-bell text-orange-400 mr-1"></i>Alert when stock drops to this level. Set 0 to disable.</p>
                        @error('low_stock_limit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if(!$product->is_unlimited_stock && $product->low_stock_limit > 0)
                <div class="mt-4 flex items-center gap-3 p-3 rounded-lg {{ $product->isLowStock() ? 'bg-orange-50 border border-orange-200' : 'bg-green-50 border border-green-200' }}">
                    <i class="fas {{ $product->isLowStock() ? 'fa-exclamation-triangle text-orange-500' : 'fa-check-circle text-green-500' }}"></i>
                    <span class="text-sm {{ $product->isLowStock() ? 'text-orange-700' : 'text-green-700' }}">
                        @if($product->isLowStock())
                            Stock ({{ $product->quantity }}) is at or below the alert limit ({{ $product->low_stock_limit }}).
                        @else
                            Stock is healthy — {{ $product->quantity }} units, limit is {{ $product->low_stock_limit }}.
                        @endif
                    </span>
                </div>
                @endif
            </div>
            <div id="unlimited-msg" class="hidden px-6 py-4 text-sm text-purple-700 bg-purple-50 flex items-center gap-2">
                <i class="fas fa-infinity text-purple-500"></i> This product has unlimited stock — no quantity tracking needed.
            </div>
        </div>

        {{-- Image --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-image text-purple-500 mr-2"></i>Product Image
                </h2>
            </div>
            <div class="p-6">
                <label for="image"
                       class="block border-2 border-dashed border-gray-300 rounded-xl text-center cursor-pointer hover:border-red-400 transition-colors overflow-hidden">
                    @if($product->image)
                        <img id="image-preview"
                             src="{{ \Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://']) ? $product->image : asset('storage/' . $product->image) }}"
                             alt="{{ $product->name }}" class="w-full h-56 object-cover">
                        <div id="image-placeholder" class="hidden py-10">
                            <i class="fas fa-cloud-upload-alt text-gray-300 text-4xl mb-3 block"></i>
                            <p class="text-sm text-gray-500 font-medium">Click to replace image</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 2MB</p>
                        </div>
                    @else
                        <img id="image-preview" src="" alt="Preview" class="hidden w-full h-56 object-cover">
                        <div id="image-placeholder" class="py-10">
                            <i class="fas fa-cloud-upload-alt text-gray-300 text-4xl mb-3 block"></i>
                            <p class="text-sm text-gray-500 font-medium">Click to upload image</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 2MB</p>
                        </div>
                    @endif
                </label>
                <input type="file" name="image" id="image" accept="image/*" class="hidden">
                <button type="button" id="remove-image"
                        class="{{ $product->image ? '' : 'hidden' }} mt-3 text-xs text-gray-500 hover:text-red-600 transition-colors"
                        onclick="event.preventDefault(); clearImage()">
                    <i class="fas fa-times mr-1"></i>Remove image
                </button>
                @error('image') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-toggle-on text-green-500 mr-2"></i>Status
                </h2>
            </div>
            <div class="p-6">
                <div class="flex gap-4">
                    <label class="relative cursor-pointer flex-1">
                        <input type="radio" name="status" value="active" class="sr-only peer" {{ old('status', $product->status) === 'active' ? 'checked' : '' }}>
                        <div class="px-4 py-3 rounded-lg border-2 border-gray-200 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                            <i class="fas fa-check-circle block text-gray-300 text-xl mb-1"></i>
                            <span class="text-sm font-semibold text-gray-600">Active</span>
                            <p class="text-xs text-gray-400 mt-0.5">Visible on POS & menu</p>
                        </div>
                    </label>
                    <label class="relative cursor-pointer flex-1">
                        <input type="radio" name="status" value="inactive" class="sr-only peer" {{ old('status', $product->status) === 'inactive' ? 'checked' : '' }}>
                        <div class="px-4 py-3 rounded-lg border-2 border-gray-200 text-center peer-checked:border-gray-500 peer-checked:bg-gray-50 transition-all">
                            <i class="fas fa-pause-circle block text-gray-300 text-xl mb-1"></i>
                            <span class="text-sm font-semibold text-gray-500">Inactive</span>
                            <p class="text-xs text-gray-400 mt-0.5">Hidden from POS & menu</p>
                        </div>
                    </label>
                </div>
                @error('status') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 pb-8">
            <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-8 py-2.5 rounded-xl font-semibold transition-colors flex items-center gap-2 text-sm shadow-sm">
                <i class="fas fa-save"></i> Update Product
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

    const unlimitedCheckbox = document.getElementById('is_unlimited_stock');
    const stockFields       = document.getElementById('stock-fields');
    const unlimitedMsg      = document.getElementById('unlimited-msg');
    const quantityInput     = document.getElementById('quantity');
    const lowStockInput     = document.getElementById('low_stock_limit');

    const toggleStock = () => {
        const u = unlimitedCheckbox.checked;
        stockFields.classList.toggle('hidden', u);
        unlimitedMsg.classList.toggle('hidden', !u);
        quantityInput.disabled = u;
        quantityInput.required = !u;
        lowStockInput.disabled = u;
        if (u) { quantityInput.value = 0; lowStockInput.value = 0; }
    };
    toggleStock();
    unlimitedCheckbox.addEventListener('change', toggleStock);

    const imageInput       = document.getElementById('image');
    const imagePreview     = document.getElementById('image-preview');
    const imagePlaceholder = document.getElementById('image-placeholder');
    const removeBtn        = document.getElementById('remove-image');

    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            imagePreview.classList.remove('hidden');
            imagePlaceholder.classList.add('hidden');
            removeBtn.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    window.clearImage = () => {
        imageInput.value = '';
        imagePreview.src = '';
        imagePreview.classList.add('hidden');
        imagePlaceholder.classList.remove('hidden');
        removeBtn.classList.add('hidden');
    };
});
</script>
@endsection
