<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'title',
        'phone_number',
        'address',
        'status',
        'tier',
        'slmc_registration_number',
        'print_tier_on_receipt',
    ];

    protected $casts = [
        'print_tier_on_receipt' => 'boolean',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tierDiscount()
    {
        return $this->belongsTo(TierDiscount::class, 'tier', 'tier');
    }

    public function tierBadgeClass(): string
    {
        $td = TierDiscount::where('tier', $this->tier)->first();
        return $td ? $td->badgeClasses() : 'bg-gray-100 text-gray-700 border-gray-300';
    }

    public function formattedName(): string
    {
        return trim(($this->title ? $this->title . ' ' : '') . $this->name);
    }
}
