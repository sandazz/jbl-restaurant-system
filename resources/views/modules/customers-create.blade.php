@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
    <div>
        <div class="mb-8 flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Add Customer</h1>
                <p class="text-gray-600 mt-1">Create a new customer record</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 max-w-2xl">
            <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Title + Name --}}
                <div class="grid grid-cols-4 gap-3">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">Title</label>
                        <select name="title" id="title"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                            <option value="">—</option>
                            <option value="Mr."    {{ old('title') === 'Mr.'    ? 'selected' : '' }}>Mr.</option>
                            <option value="Miss"   {{ old('title') === 'Miss'   ? 'selected' : '' }}>Miss</option>
                            <option value="Master" {{ old('title') === 'Master' ? 'selected' : '' }}>Master</option>
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('name') ? 'border-red-600' : '' }}"
                            placeholder="Customer full name">
                        @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="phone_number" class="block text-sm font-semibold text-gray-900 mb-2">Phone Number <span class="text-red-600">*</span></label>
                    <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('phone_number') ? 'border-red-600' : '' }}"
                        placeholder="07X XXX XXXX">
                    @error('phone_number') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-900 mb-2">Address <span class="text-gray-500 font-normal">(Optional)</span></label>
                    <textarea name="address" id="address" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Customer address">{{ old('address') }}</textarea>
                </div>

                {{-- Status + Tier --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-900 mb-2">Status <span class="text-red-600">*</span></label>
                        <select name="status" id="status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('status') ? 'border-red-600' : '' }}">
                            <option value="">Select status</option>
                            <option value="active"   {{ old('status') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tier" class="block text-sm font-semibold text-gray-900 mb-2">
                            Loyalty Tier <span class="text-red-600">*</span>
                            <a href="{{ route('tier-discounts.index') }}" target="_blank"
                               class="ml-1 text-xs text-blue-500 hover:text-blue-700 font-normal">
                                <i class="fas fa-external-link-alt"></i> Manage
                            </a>
                        </label>
                        <select name="tier" id="tier" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('tier') ? 'border-red-600' : '' }}">
                            <option value="">Select tier</option>
                            @foreach($tiers as $t)
                                <option value="{{ $t->tier }}" {{ old('tier') === $t->tier ? 'selected' : '' }}>
                                    {{ $t->tier }}{{ $t->discount_percentage > 0 ? ' ('.$t->discount_percentage.'% off)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('tier') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror

                        @if($tiers->isEmpty())
                            <p class="text-xs text-orange-600 mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                No tiers configured.
                                <a href="{{ route('tier-discounts.index') }}" class="underline">Add tiers first.</a>
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Live badge preview --}}
                <div id="tier-preview" class="flex items-center gap-2 text-sm text-gray-500">
                    Selected tier:
                    <span id="tier-badge-preview" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border bg-gray-100 text-gray-500 border-gray-300">
                        None
                    </span>
                    <span id="tier-discount-hint" class="text-xs text-green-600 font-medium hidden"></span>
                </div>

                <div>
                    <label for="slmc_registration_number" class="block text-sm font-semibold text-gray-900 mb-2">
                        SLMC Registration No. <span class="text-gray-500 font-normal">(Optional)</span>
                    </label>
                    <input type="text" name="slmc_registration_number" id="slmc_registration_number"
                        value="{{ old('slmc_registration_number') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="e.g. SLMC-123456">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="print_tier_on_receipt" id="print_tier_on_receipt" value="1"
                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                        {{ old('print_tier_on_receipt') ? 'checked' : '' }}>
                    <label for="print_tier_on_receipt" class="text-sm font-medium text-gray-900">
                        Print loyalty tier on receipt
                    </label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tier badge color data from DB --}}
    @php
    $tierData = $tiers->mapWithKeys(fn($t) => [$t->tier => [
        'color'    => $t->color,
        'discount' => (float) $t->discount_percentage,
        'classes'  => $t->badgeClasses(),
    ]]);
    @endphp

    <script>
        const TIER_DATA = @json($tierData);

        const tierSelect  = document.getElementById('tier');
        const tierPreview = document.getElementById('tier-badge-preview');
        const discHint    = document.getElementById('tier-discount-hint');

        function updateBadge(tier) {
            const data = TIER_DATA[tier];
            const base = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border';

            if (data) {
                // Map color name to inline style (Tailwind purge-safe approach uses style attr for dynamic colors)
                const colorStyles = {
                    yellow: { bg:'#fef9c3', color:'#854d0e', border:'#fde047' },
                    blue:   { bg:'#dbeafe', color:'#1e40af', border:'#93c5fd' },
                    purple: { bg:'#f3e8ff', color:'#6b21a8', border:'#c084fc' },
                    gray:   { bg:'#f1f5f9', color:'#475569', border:'#94a3b8' },
                    green:  { bg:'#dcfce7', color:'#166534', border:'#86efac' },
                    red:    { bg:'#fee2e2', color:'#991b1b', border:'#fca5a5' },
                    orange: { bg:'#ffedd5', color:'#9a3412', border:'#fdba74' },
                    indigo: { bg:'#e0e7ff', color:'#3730a3', border:'#a5b4fc' },
                    pink:   { bg:'#fce7f3', color:'#9d174d', border:'#f9a8d4' },
                    teal:   { bg:'#ccfbf1', color:'#134e4a', border:'#5eead4' },
                };
                const s = colorStyles[data.color] || colorStyles.blue;
                tierPreview.className = base;
                tierPreview.style.background   = s.bg;
                tierPreview.style.color        = s.color;
                tierPreview.style.borderColor  = s.border;
                tierPreview.textContent        = tier;

                discHint.textContent = data.discount > 0 ? `${data.discount}% discount applied at POS` : '';
                discHint.classList.toggle('hidden', data.discount === 0);
            } else {
                tierPreview.className = `${base} bg-gray-100 text-gray-500 border-gray-300`;
                tierPreview.style = '';
                tierPreview.textContent = 'None';
                discHint.classList.add('hidden');
            }
        }

        tierSelect.addEventListener('change', e => updateBadge(e.target.value));
        updateBadge(tierSelect.value);
    </script>
@endsection
