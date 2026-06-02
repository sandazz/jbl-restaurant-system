@extends('layouts.app')
@section('title', 'Close Shift — Cash Reconciliation')

@section('content')
<div class="px-6 py-8">
    @if($openShift)
        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('clerk-balancings.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold inline-flex items-center gap-1 mb-4">
                <i class="fas fa-arrow-left"></i>Back to History
            </a>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Close Shift — Cash Reconciliation</h1>
            <p class="text-gray-600 text-sm">Count physical cash by denomination and reconcile your till</p>
        </div>

        {{-- Shift Info Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Shift Opened</p>
                    <p class="text-lg font-bold text-gray-900">{{ $openShift->shift_start->format('d M Y') }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $openShift->shift_start->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Duration</p>
                    <p class="text-lg font-bold text-gray-900">
                        @php
                            $duration = $openShift->shift_start->diffForHumans(null, true);
                        @endphp
                        {{ $duration }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status</p>
                    <p>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                            Open
                        </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <p class="font-semibold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Denomination Form --}}
        <form action="{{ route('clerk-balancings.close', $openShift) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Denomination Table --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-900">Denomination Count</h2>
                    <p class="text-sm text-gray-600 mt-1">Enter the quantity of each note and coin in the till</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Denomination</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">Quantity</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900"></th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- BANKNOTES SECTION --}}
                            <tr class="bg-gray-50/50">
                                <td colspan="4" class="px-6 py-2">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-money-bill-wave text-green-500 mr-1"></i>Banknotes
                                    </span>
                                </td>
                            </tr>

                            @foreach(['5000', '2000', '1000', '500', '200', '100', '50', '20', '10'] as $denom)
                                @php
                                    $field = 'note_' . $denom;
                                    $value = intval($denom);
                                    $displayValue = number_format($value, 0);
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-800">Rs. {{ $displayValue }}</span>
                                        <span class="ml-2 text-xs text-gray-400">note</span>
                                    </td>
                                    <td class="px-6 py-4 w-40">
                                        <input type="number"
                                               name="{{ $field }}"
                                               id="{{ $field }}"
                                               value="{{ old($field, 0) }}"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-center text-sm font-semibold focus:ring-2 focus:ring-red-500 focus:border-transparent denom-input transition-all"
                                               data-value="{{ $value }}"
                                               data-subtotal="sub_{{ $field }}"
                                               @error($field) class="border-red-500" @enderror>
                                    </td>
                                    <td class="px-3 py-4 text-gray-400 font-medium text-center">=</td>
                                    <td class="px-6 py-4 text-right">
                                        <span id="sub_{{ $field }}" class="font-semibold text-gray-800">Rs. 0.00</span>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- COINS SECTION --}}
                            <tr class="bg-gray-50/50">
                                <td colspan="4" class="px-6 py-2">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-coins text-yellow-500 mr-1"></i>Coins
                                    </span>
                                </td>
                            </tr>

                            @foreach(['5', '2', '1', '0.50'] as $coin)
                                @php
                                    $field = $coin === '0.50' ? 'coin_50c' : 'coin_' . $coin;
                                    $value = floatval($coin);
                                    $displayValue = $coin === '0.50' ? '50 Cents' : 'Rs. ' . $coin;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-800">{{ $displayValue }}</span>
                                        <span class="ml-2 text-xs text-gray-400">coin</span>
                                    </td>
                                    <td class="px-6 py-4 w-40">
                                        <input type="number"
                                               name="{{ $field }}"
                                               id="{{ $field }}"
                                               value="{{ old($field, 0) }}"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-center text-sm font-semibold focus:ring-2 focus:ring-red-500 focus:border-transparent denom-input transition-all"
                                               data-value="{{ $value }}"
                                               data-subtotal="sub_{{ $field }}"
                                               @error($field) class="border-red-500" @enderror>
                                    </td>
                                    <td class="px-3 py-4 text-gray-400 font-medium text-center">=</td>
                                    <td class="px-6 py-4 text-right">
                                        <span id="sub_{{ $field }}" class="font-semibold text-gray-800">Rs. 0.00</span>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- GRAND TOTAL --}}
                            <tr class="bg-gray-50 border-t-2 border-gray-300">
                                <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900 text-lg">
                                    Total Physical Cash
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span id="grand_total" class="text-2xl font-bold text-red-600">Rs. 0.00</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Notes Field --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                    Notes <span class="text-gray-500 font-normal">(Optional)</span>
                </label>
                <textarea name="notes"
                          rows="3"
                          placeholder="Any remarks about this shift... (e.g., 'Found 200 rupees under register', 'System went down 2:30-2:45pm')"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ old('notes') }}</textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-check-circle"></i> Close Shift & Reconcile
                </button>
                <a href="{{ route('clerk-balancings.index') }}"
                   class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-3 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>

    @else
        {{-- No Open Shift State --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
            <i class="fas fa-door-open text-gray-300 text-6xl mb-6 inline-block"></i>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">No Open Shift</h3>
            <p class="text-gray-600 text-base mb-8">
                You don't have an active shift. Open a new shift to begin tracking sales and reconciliation.
            </p>

            {{-- Opening Cash Input Form --}}
            <form action="{{ route('clerk-balancings.store') }}" method="POST" class="max-w-md mx-auto mb-8">
                @csrf
                <div class="mb-6">
                    <label for="opening_amount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Opening Cash Amount
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600 font-medium pointer-events-none">Rs.</span>
                        <input type="number"
                               name="opening_amount"
                               id="opening_amount"
                               value="{{ old('opening_amount', 0) }}"
                               step="0.01"
                               min="0"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5"><i class="fas fa-info-circle mr-1"></i>Enter the opening float/till amount</p>
                    @error('opening_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold text-sm transition-colors">
                    <i class="fas fa-play"></i>Open New Shift
                </button>
            </form>

            <div class="flex gap-4 justify-center">
                <a href="{{ route('clerk-balancings.index') }}"
                   class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold text-sm transition-colors">
                    <i class="fas fa-arrow-left"></i>View History
                </a>
            </div>
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.denom-input');

    function formatLKR(amount) {
        return 'Rs. ' + amount.toLocaleString('en-LK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function recalculate() {
        let grand = 0;
        inputs.forEach(function (input) {
            const qty = parseInt(input.value, 10) || 0;
            const val = parseFloat(input.dataset.value) || 0;
            const sub = qty * val;
            grand += sub;
            const span = document.getElementById(input.dataset.subtotal);
            if (span) {
                span.textContent = formatLKR(sub);
            }
        });
        const grandEl = document.getElementById('grand_total');
        if (grandEl) {
            grandEl.textContent = formatLKR(grand);
        }
    }

    inputs.forEach(function (input) {
        input.addEventListener('input', recalculate);
        input.addEventListener('change', recalculate);
    });

    // Run on load to apply old() values
    recalculate();
});
</script>
@endsection
