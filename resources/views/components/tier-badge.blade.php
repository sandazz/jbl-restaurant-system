@props(['tier', 'customer' => null])

@php
// Resolve classes from TierDiscount table if a customer model is passed,
// otherwise fall back to a plain lookup by tier name.
if ($customer instanceof \App\Models\Customer) {
    $classes = $customer->tierBadgeClass();
} else {
    $td = \App\Models\TierDiscount::where('tier', $tier)->first();
    $classes = $td ? $td->badgeClasses() : 'bg-gray-100 text-gray-700 border-gray-300';
}
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border {$classes}"
]) }}>
    {{ $tier }}
</span>

{{--
    Usage:
        With a Customer model (resolves color from DB):
            <x-tier-badge :tier="$customer->tier" :customer="$customer" />

        Name only (still does a DB lookup):
            <x-tier-badge tier="VIP" />

        On receipt (conditional):
            @if($customer->print_tier_on_receipt)
                <x-tier-badge :tier="$customer->tier" :customer="$customer" />
            @endif
--}}
