@extends('layouts.app')
@section('title', 'Shift & Till Management')

@section('content')
<div class="px-6 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Shift & Till Management</h1>
            <p class="text-gray-600 text-sm">Shift records and cash reconciliation</p>
        </div>
        <form action="{{ route('clerk-balancings.store') }}" method="POST" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold text-sm transition-colors">
                <i class="fas fa-play"></i>Open New Shift
            </button>
        </form>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-start gap-3">
            <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->has('shift'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-start gap-3">
            <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
            <p>{{ $errors->first('shift') }}</p>
        </div>
    @endif

    {{-- Warning Banner for Open Shift --}}
    @php
        $userOpenShift = $records->getCollection()
            ->firstWhere(fn($r) => $r->user_id === auth()->id() && $r->isOpen());
    @endphp
    @if($userOpenShift)
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm flex items-start gap-3 justify-between">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                <div>
                    <p class="font-semibold">You have an open shift</p>
                    <p class="text-xs mt-1 opacity-80">Opened at {{ $userOpenShift->shift_start?->format('H:i') }}</p>
                </div>
            </div>
            <a href="{{ route('clerk-balancings.create') }}" class="text-amber-700 hover:text-amber-900 font-semibold text-xs whitespace-nowrap ml-4">
                Close Now →
            </a>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($records->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 tracking-wider">Cashier</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 tracking-wider">Shift Start</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 tracking-wider">Shift End</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900 tracking-wider">Expected Cash</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900 tracking-wider">Expected Card</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900 tracking-wider">Physical Cash</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900 tracking-wider">Variance</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900 tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900 tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($records as $record)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono">#{{ $record->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $record->user->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $record->shift_start?->format('d M Y, H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if($record->shift_end)
                                        {{ $record->shift_end->format('d M Y, H:i') }}
                                    @else
                                        <span class="text-amber-600 font-semibold text-xs">Active</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900 font-semibold">
                                    LKR {{ number_format($record->expected_cash_total, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900 font-semibold">
                                    LKR {{ number_format($record->expected_card_total, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900 font-semibold">
                                    LKR {{ number_format($record->physical_cash_total, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    @if($record->status === 'open')
                                        <span class="text-gray-400 text-xs italic">Pending</span>
                                    @else
                                        @php
                                            $badgeClass = match($record->variance_type) {
                                                'shortage' => 'bg-red-100 text-red-700',
                                                'excess' => 'bg-green-100 text-green-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            {{ ucfirst($record->variance_type) }}: {{ number_format($record->variance, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    @php
                                        $statusClass = $record->status === 'open'
                                            ? 'bg-amber-100 text-amber-700'
                                            : 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="{{ route('clerk-balancings.show', $record) }}"
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-xs inline-flex items-center gap-1">
                                        <i class="fas fa-eye"></i>View
                                    </a>
                                    @if($record->isOpen() && $record->user_id === auth()->id())
                                        <a href="{{ route('clerk-balancings.create') }}"
                                           class="text-red-600 hover:text-red-800 font-semibold text-xs ml-3 inline-flex items-center gap-1">
                                            <i class="fas fa-door-open"></i>Close
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $records->links('pagination::tailwind') }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="px-6 py-16 text-center">
                <i class="fas fa-cash-register text-gray-300 text-6xl mb-4 inline-block"></i>
                <h3 class="text-xl font-semibold text-gray-900 mt-4 mb-2">No shifts recorded yet</h3>
                <p class="text-gray-600 text-sm mb-6">Open a new shift to start tracking cash and reconciliation</p>
                <form action="{{ route('clerk-balancings.store') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold text-sm transition-colors">
                        <i class="fas fa-play"></i>Open New Shift
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
