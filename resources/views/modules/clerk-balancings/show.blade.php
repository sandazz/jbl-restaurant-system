@extends('layouts.app')
@section('title', 'Shift #' . $clerkBalancing->id)

@section('styles')
<style>
    @media print {
        .sidebar { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .print-hide { display: none !important; }
    }
</style>
@endsection

@section('content')
<div class="px-6 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex-1">
            <a href="{{ route('clerk-balancings.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold inline-flex items-center gap-1 mb-4 print-hide">
                <i class="fas fa-arrow-left"></i>Back to History
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-4xl font-bold text-gray-900">Shift #{{ $clerkBalancing->id }}</h1>
                @php
                    $statusClass = $clerkBalancing->status === 'open'
                        ? 'bg-amber-100 text-amber-700'
                        : 'bg-gray-100 text-gray-600';
                @endphp
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                    {{ ucfirst($clerkBalancing->status) }}
                </span>
            </div>
            <p class="text-gray-600 text-sm mt-2">
                <strong>{{ $clerkBalancing->user->name }}</strong> •
                {{ $clerkBalancing->shift_start?->format('d M Y') }}
            </p>
        </div>
        <button onclick="window.print()" class="print-hide inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-semibold text-sm transition-colors">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-start gap-3 print-hide">
            <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- 5 Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {{-- Opening Cash --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Opening Cash</p>
            <p class="text-2xl font-bold text-gray-900">LKR {{ number_format($clerkBalancing->opening_amount, 2) }}</p>
            <div class="w-8 h-1 rounded-full bg-indigo-500 mt-4"></div>
        </div>

        {{-- Expected Cash --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Cash Sales</p>
            <p class="text-2xl font-bold text-gray-900">LKR {{ number_format($clerkBalancing->expected_cash_total, 2) }}</p>
            <div class="w-8 h-1 rounded-full bg-blue-500 mt-4"></div>
        </div>

        {{-- Expected Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Card / Transfer</p>
            <p class="text-2xl font-bold text-gray-900">LKR {{ number_format($clerkBalancing->expected_card_total, 2) }}</p>
            <div class="w-8 h-1 rounded-full bg-purple-500 mt-4"></div>
        </div>

        {{-- Physical Cash --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Physical Cash</p>
            <p class="text-2xl font-bold text-gray-900">LKR {{ number_format($clerkBalancing->physical_cash_total, 2) }}</p>
            <div class="w-8 h-1 rounded-full bg-green-500 mt-4"></div>
        </div>

        {{-- Variance --}}
        @php
            $varianceClass = match($clerkBalancing->variance_type) {
                'shortage' => ['border-red-200 bg-white', 'text-red-600', 'bg-red-500'],
                'excess' => ['border-green-200 bg-white', 'text-green-600', 'bg-green-500'],
                default => ['border-gray-200 bg-white', 'text-gray-600', 'bg-gray-400'],
            };
        @endphp
        <div class="rounded-2xl border shadow-sm p-5 {{ $varianceClass[0] }}">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Variance</p>
            <p class="text-2xl font-bold {{ $varianceClass[1] }}">LKR {{ number_format($clerkBalancing->variance, 2) }}</p>
            <div class="w-8 h-1 rounded-full {{ $varianceClass[2] }} mt-4"></div>
        </div>
    </div>

    {{-- Shift Timeline Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Shift Timeline</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Opened</p>
                <p class="text-base font-semibold text-gray-900">{{ $clerkBalancing->shift_start->format('d M Y') }}</p>
                <p class="text-sm text-gray-600">{{ $clerkBalancing->shift_start->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Closed</p>
                @if($clerkBalancing->shift_end)
                    <p class="text-base font-semibold text-gray-900">{{ $clerkBalancing->shift_end->format('d M Y') }}</p>
                    <p class="text-sm text-gray-600">{{ $clerkBalancing->shift_end->format('H:i') }}</p>
                @else
                    <p class="text-base font-semibold text-amber-600">Still open</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Duration</p>
                @if($clerkBalancing->shift_end)
                    <p class="text-base font-semibold text-gray-900">
                        {{ $clerkBalancing->shift_end->diffForHumans($clerkBalancing->shift_start, true) }}
                    </p>
                @else
                    <p class="text-base font-semibold text-amber-600">
                        {{ now()->diffForHumans($clerkBalancing->shift_start, true) }}
                    </p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Cashier</p>
                <p class="text-base font-semibold text-gray-900">{{ $clerkBalancing->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $clerkBalancing->user->email }}</p>
            </div>
        </div>
    </div>

    {{-- Denomination Breakdown (only if closed) --}}
    @if($clerkBalancing->status === 'closed')
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-900">Cash Count Breakdown</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Denomination</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">Type</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">Qty Counted</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $denoms = [
                                ['label'=>'Rs. 5,000', 'type'=>'Note', 'field'=>'note_5000', 'value'=>5000],
                                ['label'=>'Rs. 2,000', 'type'=>'Note', 'field'=>'note_2000', 'value'=>2000],
                                ['label'=>'Rs. 1,000', 'type'=>'Note', 'field'=>'note_1000', 'value'=>1000],
                                ['label'=>'Rs. 500', 'type'=>'Note', 'field'=>'note_500', 'value'=>500],
                                ['label'=>'Rs. 200', 'type'=>'Note', 'field'=>'note_200', 'value'=>200],
                                ['label'=>'Rs. 100', 'type'=>'Note', 'field'=>'note_100', 'value'=>100],
                                ['label'=>'Rs. 50', 'type'=>'Note', 'field'=>'note_50', 'value'=>50],
                                ['label'=>'Rs. 20', 'type'=>'Note', 'field'=>'note_20', 'value'=>20],
                                ['label'=>'Rs. 10', 'type'=>'Note', 'field'=>'note_10', 'value'=>10],
                                ['label'=>'Rs. 5', 'type'=>'Coin', 'field'=>'coin_5', 'value'=>5],
                                ['label'=>'Rs. 2', 'type'=>'Coin', 'field'=>'coin_2', 'value'=>2],
                                ['label'=>'Rs. 1', 'type'=>'Coin', 'field'=>'coin_1', 'value'=>1],
                                ['label'=>'50 Cents', 'type'=>'Coin', 'field'=>'coin_50c', 'value'=>0.5],
                            ];
                        @endphp

                        @foreach($denoms as $d)
                            @php
                                $qty = $clerkBalancing->{$d['field']};
                                $sub = $qty * $d['value'];
                            @endphp
                            <tr class="{{ $qty > 0 ? '' : 'opacity-40' }} hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-800">{{ $d['label'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-medium {{ $d['type']==='Note' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $d['type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-gray-700">{{ $qty }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-800">
                                    LKR {{ number_format($sub, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-300">
                            <td colspan="3" class="px-6 py-4 font-bold text-gray-900 text-right">Total</td>
                            <td class="px-6 py-4 text-right font-bold text-red-600 text-lg">
                                LKR {{ number_format($clerkBalancing->physical_cash_total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- Variance Alert Box --}}
    @if($clerkBalancing->status === 'closed')
        @php
            $alertClass = match($clerkBalancing->variance_type) {
                'shortage' => ['bg-red-50 border-red-200', 'text-red-800', 'fa-exclamation-triangle text-red-500'],
                'excess' => ['bg-green-50 border-green-200', 'text-green-800', 'fa-arrow-up-circle text-green-500'],
                default => ['bg-gray-50 border-gray-200', 'text-gray-700', 'fa-check-circle text-gray-400'],
            };
            $alertMsg = match($clerkBalancing->variance_type) {
                'shortage' => 'Shortage of LKR ' . number_format($clerkBalancing->variance, 2) . '. Physical cash is less than expected sales.',
                'excess' => 'Excess of LKR ' . number_format($clerkBalancing->variance, 2) . '. Physical cash is more than expected sales.',
                default => 'Till is balanced. Physical cash matches expected sales exactly.',
            };
        @endphp
        <div class="rounded-xl border p-6 mb-6 flex items-start gap-4 {{ $alertClass[0] }}">
            <i class="fas {{ $alertClass[2] }} text-xl mt-1 flex-shrink-0"></i>
            <div>
                <p class="font-semibold text-lg {{ $alertClass[1] }}">{{ ucfirst($clerkBalancing->variance_type) }}</p>
                <p class="text-sm mt-2 {{ $alertClass[1] }}">{{ $alertMsg }}</p>
            </div>
        </div>
    @endif

    {{-- Notes Section --}}
    @if($clerkBalancing->notes)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Notes</h2>
            <p class="text-gray-700 text-sm whitespace-pre-line">{{ $clerkBalancing->notes }}</p>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex gap-3 print-hide">
        @if($clerkBalancing->isOpen() && $clerkBalancing->user_id === auth()->id())
            <a href="{{ route('clerk-balancings.create') }}"
               class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold text-sm transition-colors">
                <i class="fas fa-door-open"></i>Close Shift
            </a>
        @endif
        <a href="{{ route('clerk-balancings.index') }}"
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold text-sm transition-colors">
            <i class="fas fa-arrow-left"></i>Back to History
        </a>
    </div>
</div>
@endsection
