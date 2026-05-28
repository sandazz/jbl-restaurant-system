<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'unit',
        'current_balance',
        'reorder_level',
        'cost_per_unit',
        'supplier_id',
        'notes',
        'status',
    ];

    protected $casts = [
        'current_balance' => 'decimal:3',
        'reorder_level'   => 'decimal:3',
        'cost_per_unit'   => 'decimal:2',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /** Menu items (products) that consume this raw material */
    public function menuItems()
    {
        return $this->belongsToMany(Product::class, 'menu_item_ingredient')
            ->withPivot('quantity_required')
            ->withTimestamps();
    }

    public function isLowStock(): bool
    {
        return $this->current_balance <= $this->reorder_level;
    }
}
