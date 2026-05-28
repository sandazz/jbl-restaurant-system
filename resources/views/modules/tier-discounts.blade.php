@extends('layouts.app')

@section('title', 'Loyalty Tiers & Discounts')

@section('content')
<div>

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('settings.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <h1 class="text-4xl font-bold text-gray-900">Loyalty Tiers & Discounts</h1>
            </div>
            <p class="text-gray-600 mt-1 ml-7">Create and manage the tiers shown in customer profiles and applied automatically at POS.</p>
        </div>
    </div>

    {{-- Toast notification (fixed top-right, auto-dismisses) --}}
    @if(session('success'))
    <div id="toast-success"
         style="position:fixed; top:1.25rem; right:1.25rem; z-index:9999; display:flex; align-items:flex-start; gap:12px;
                background:#fff; border:1px solid #bbf7d0; border-left:4px solid #16a34a; border-radius:10px;
                padding:14px 18px; box-shadow:0 8px 24px rgba(0,0,0,0.10); min-width:280px; max-width:360px;
                transform:translateX(120%); transition:transform 0.35s cubic-bezier(.4,0,.2,1);">
        <div style="color:#16a34a; font-size:1.2rem; margin-top:1px; flex-shrink:0;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div style="flex:1;">
            <p style="font-weight:700; font-size:0.82rem; color:#14532d; margin:0 0 2px;">Saved successfully</p>
            <p style="font-size:0.78rem; color:#166534; margin:0;">{{ session('success') }}</p>
            {{-- Progress bar --}}
            <div style="margin-top:8px; height:3px; background:#dcfce7; border-radius:2px; overflow:hidden;">
                <div id="toast-progress"
                     style="height:100%; width:100%; background:#16a34a; border-radius:2px;
                            transition:width 3s linear;"></div>
            </div>
        </div>
        <button onclick="dismissToast()" style="background:none; border:none; cursor:pointer; color:#86efac; font-size:0.9rem; margin-top:1px; flex-shrink:0;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        (function () {
            const toast    = document.getElementById('toast-success');
            const progress = document.getElementById('toast-progress');
            let timer;

            // Define FIRST so the setTimeout reference below and the onclick both resolve it
            window.dismissToast = function () {
                clearTimeout(timer);
                toast.style.transform = 'translateX(120%)';
                setTimeout(() => toast.remove(), 400);
            };

            // Slide in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => { toast.style.transform = 'translateX(0)'; });
            });
            // Drain progress bar
            setTimeout(() => { progress.style.width = '0%'; }, 50);
            // Auto-dismiss after 3 s
            timer = setTimeout(window.dismissToast, 3200);
        })();
    </script>
    @endif

    @if($errors->has('delete'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first('delete') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── LEFT: Existing tiers ── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900 text-lg">Existing Tiers</h2>
                    <span class="text-xs text-gray-500">{{ $tiers->count() }} tier(s) configured</span>
                </div>

                @if($tiers->isEmpty())
                    <div class="text-center py-12 text-gray-400">
                        <i class="fas fa-tags text-4xl mb-3 block"></i>
                        <p class="text-sm">No tiers yet. Add your first tier on the right.</p>
                    </div>
                @else
                {{-- Single shared delete form — lives OUTSIDE saveAllForm to avoid nested-form breakage --}}
                <form id="deleteForm" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>

                <form id="saveAllForm" action="{{ route('tier-discounts.save-all') }}" method="POST">
                    @csrf

                    <div class="divide-y divide-gray-50">
                        @foreach($tiers as $i => $tier)
                        @php
                            $c = \App\Models\TierDiscount::COLOR_MAP[$tier->color] ?? \App\Models\TierDiscount::COLOR_MAP['blue'];
                            $customerCount = $tier->customerCount();
                        @endphp
                        <input type="hidden" name="tiers[{{ $i }}][id]" value="{{ $tier->id }}">

                        <div class="flex items-center gap-4 px-6 py-4">

                            {{-- Badge --}}
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border {{ $c['bg'] }} {{ $c['text'] }} {{ $c['border'] }} w-28 justify-center flex-shrink-0">
                                {{ $tier->tier }}
                            </span>

                            {{-- Customer count --}}
                            <span class="text-xs text-gray-400 w-16 flex-shrink-0">
                                {{ $customerCount }} customer{{ $customerCount !== 1 ? 's' : '' }}
                            </span>

                            {{-- Discount % --}}
                            <div class="flex items-center gap-1 flex-1">
                                <input type="number"
                                       name="tiers[{{ $i }}][discount_percentage]"
                                       value="{{ old("tiers.{$i}.discount_percentage", $tier->discount_percentage) }}"
                                       min="0" max="100" step="0.5"
                                       class="tier-field w-20 px-2 py-1.5 border border-gray-300 rounded-lg text-sm text-center font-semibold focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <span class="text-gray-500 text-sm">%</span>
                            </div>

                            {{-- Color --}}
                            <select name="tiers[{{ $i }}][color]"
                                    class="tier-field text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white">
                                @foreach(array_keys(\App\Models\TierDiscount::COLOR_MAP) as $colorOption)
                                    <option value="{{ $colorOption }}" {{ $tier->color === $colorOption ? 'selected' : '' }}>
                                        {{ ucfirst($colorOption) }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Active toggle --}}
                            <label class="flex items-center gap-1.5 cursor-pointer flex-shrink-0">
                                <input type="checkbox" name="tiers[{{ $i }}][is_active]" value="1"
                                       class="tier-field w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                       {{ $tier->is_active ? 'checked' : '' }}>
                                <span class="text-xs text-gray-600">Active</span>
                            </label>

                            {{-- Delete — type="button" so it never submits saveAllForm --}}
                            <button type="button"
                                    onclick="confirmDelete('{{ route('tier-discounts.destroy', $tier) }}', '{{ addslashes($tier->tier) }}', {{ $customerCount }})"
                                    class="text-gray-300 hover:text-red-500 transition-colors p-1"
                                    title="Delete tier">
                                <i class="fas fa-trash text-sm"></i>
                            </button>

                        </div>
                        @endforeach
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex items-center gap-3">
                        <button id="saveBtn" type="submit" disabled
                                class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold transition-all
                                       bg-gray-200 text-gray-400 cursor-not-allowed">
                            <i class="fas fa-save"></i>
                            <span id="saveBtnLabel">No changes</span>
                        </button>
                        <p class="text-xs text-gray-400">Edit any row above to enable saving.</p>
                    </div>
                </form>

                <script>
                // ── Delete handler (uses the shared form outside saveAllForm) ──
                function confirmDelete(url, tierName, customerCount) {
                    let msg = 'Delete tier "' + tierName + '"?';
                    if (customerCount > 0) msg += '\n⚠ ' + customerCount + ' customer(s) are assigned to this tier.';
                    if (!confirm(msg)) return;
                    const form = document.getElementById('deleteForm');
                    form.action = url;
                    form.submit();
                }

                // ── Save-button activation on first change ──
                (function () {
                    const btn   = document.getElementById('saveBtn');
                    const label = document.getElementById('saveBtnLabel');
                    let changed = 0;

                    function markChanged() {
                        changed++;
                        label.textContent = changed === 1 ? 'Save 1 change' : 'Save ' + changed + ' changes';
                        btn.disabled  = false;
                        btn.className = 'flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold transition-all bg-red-600 hover:bg-red-700 text-white cursor-pointer';
                    }

                    document.querySelectorAll('.tier-field')
                            .forEach(el => el.addEventListener('change', markChanged));
                })();
                </script>
                @endif
            </div>
        </div>

        {{-- ── RIGHT: Add new tier ── --}}
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 sticky top-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900 text-lg">Add New Tier</h2>
                    <p class="text-xs text-gray-500 mt-0.5">This tier will appear in customer forms and the POS discount.</p>
                </div>

                <form action="{{ route('tier-discounts.store') }}" method="POST" class="px-6 py-5 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">
                            Tier Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="tier" value="{{ old('tier') }}"
                               placeholder="e.g. Premium, Gold, Silver…"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent {{ $errors->has('tier') ? 'border-red-500' : '' }}">
                        @error('tier')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">
                            Discount % <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="discount_percentage" value="{{ old('discount_percentage', 0) }}"
                                   min="0" max="100" step="0.5"
                                   class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center font-semibold focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <span class="text-gray-500 text-sm">% off subtotal</span>
                        </div>
                        @error('discount_percentage')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Badge Color</label>
                        <div class="grid grid-cols-5 gap-2">
                            @foreach(\App\Models\TierDiscount::COLOR_MAP as $colorKey => $classes)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="color" value="{{ $colorKey }}"
                                       class="sr-only peer"
                                       {{ old('color', 'blue') === $colorKey ? 'checked' : '' }}>
                                <span class="flex items-center justify-center h-8 rounded-lg border-2 text-xs font-bold
                                             peer-checked:border-gray-800 border-transparent
                                             {{ $classes['bg'] }} {{ $classes['text'] }}"
                                      title="{{ ucfirst($colorKey) }}">
                                    Aa
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @error('color')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="new_is_active" value="1"
                               class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" checked>
                        <label for="new_is_active" class="text-sm text-gray-700">Active (visible in forms & POS)</label>
                    </div>

                    <button type="submit"
                            class="w-full bg-gray-900 hover:bg-gray-700 text-white py-2 rounded-lg text-sm font-semibold transition-colors">
                        <i class="fas fa-plus mr-1.5"></i>Add Tier
                    </button>
                </form>
            </div>

            {{-- Info box --}}
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs text-blue-800 space-y-1">
                <p class="font-semibold">How it works</p>
                <p>• Tiers you add here appear in the customer <strong>Create</strong> and <strong>Edit</strong> forms.</p>
                <p>• When a cashier selects a customer at POS, the tier discount is applied automatically.</p>
                <p>• Inactive tiers are hidden from new selections but kept on existing customers.</p>
                <p>• You cannot delete a tier that has customers assigned to it.</p>
            </div>
        </div>

    </div>
</div>
@endsection
