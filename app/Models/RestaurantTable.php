<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $table = 'restaurant_tables';

    protected $fillable = [
        'table_number',
        'name',
        'capacity',
        'status',
        'section',
        'occupied_at',
    ];

    protected $casts = [
        'occupied_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class, 'table_id')
            ->whereIn('status', ['pending', 'confirmed', 'hold'])
            ->latest();
    }

    public function activeOrders()
    {
        return $this->hasMany(Order::class, 'table_id')
            ->whereIn('status', ['pending', 'confirmed', 'hold'])
            ->latest();
    }

    /** Minutes elapsed since table became occupied. Returns null when not occupied. */
    public function elapsedMinutes(): ?int
    {
        if (!$this->occupied_at) {
            return null;
        }

        return (int) $this->occupied_at->diffInMinutes(now());
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function markOccupied(): void
    {
        $this->update(['status' => 'occupied', 'occupied_at' => now()]);
    }

    public function markAvailable(): void
    {
        $this->update(['status' => 'available', 'occupied_at' => null]);
    }

    public function markBilling(): void
    {
        $this->update(['status' => 'billing']);
    }
}
