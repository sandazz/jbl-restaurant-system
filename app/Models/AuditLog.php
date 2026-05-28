<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'order_id',
        'action_type',
        'cashier_id',
        'authorizing_manager_id',
        'remarks',
        'meta',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function authorizingManager()
    {
        return $this->belongsTo(User::class, 'authorizing_manager_id');
    }

    /** Scope: records created today */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
