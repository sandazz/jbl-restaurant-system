<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClerkBalancing extends Model
{
    protected $fillable = [
        'user_id',
        'shift_start',
        'shift_end',
        'note_5000', 'note_2000', 'note_1000', 'note_500', 'note_200',
        'note_100', 'note_50', 'note_20', 'note_10',
        'coin_5', 'coin_2', 'coin_1', 'coin_50c',
        'physical_cash_total',
        'expected_cash_total',
        'expected_card_total',
        'variance',
        'variance_type',
        'notes',
        'status',
    ];

    protected $casts = [
        'shift_start'          => 'datetime',
        'shift_end'            => 'datetime',
        'physical_cash_total'  => 'decimal:2',
        'expected_cash_total'  => 'decimal:2',
        'expected_card_total'  => 'decimal:2',
        'variance'             => 'decimal:2',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
