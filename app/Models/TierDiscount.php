<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TierDiscount extends Model
{
    protected $fillable = ['tier', 'discount_percentage', 'color', 'is_active'];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_active'           => 'boolean',
    ];

    /** Available badge colors and their Tailwind classes */
    public const COLOR_MAP = [
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-300'],
        'blue'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'border' => 'border-blue-300'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-300'],
        'gray'   => ['bg' => 'bg-gray-100',   'text' => 'text-gray-700',   'border' => 'border-gray-300'],
        'green'  => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'border' => 'border-green-300'],
        'red'    => ['bg' => 'bg-red-100',    'text' => 'text-red-800',    'border' => 'border-red-300'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-300'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'border' => 'border-indigo-300'],
        'pink'   => ['bg' => 'bg-pink-100',   'text' => 'text-pink-800',   'border' => 'border-pink-300'],
        'teal'   => ['bg' => 'bg-teal-100',   'text' => 'text-teal-800',   'border' => 'border-teal-300'],
    ];

    public function badgeClasses(): string
    {
        $c = self::COLOR_MAP[$this->color] ?? self::COLOR_MAP['blue'];
        return "{$c['bg']} {$c['text']} {$c['border']}";
    }

    /** Active tiers ordered by id, ready for dropdowns */
    public static function forDropdown()
    {
        return static::where('is_active', true)->orderBy('id')->get();
    }

    /** Keyed map for POS JS injection: ['VIP' => 15.0, ...] */
    public static function activeMap(): array
    {
        return static::where('is_active', true)
            ->pluck('discount_percentage', 'tier')
            ->map(fn($v) => (float) $v)
            ->toArray();
    }

    public function customerCount(): int
    {
        return Customer::where('tier', $this->tier)->count();
    }
}
