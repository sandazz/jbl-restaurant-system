<?php

namespace Database\Seeders;

use App\Models\TierDiscount;
use Illuminate\Database\Seeder;

class TierDiscountSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['tier' => 'VIP',      'discount_percentage' => 15.00, 'color' => 'yellow', 'is_active' => true],
            ['tier' => 'Moderate', 'discount_percentage' => 10.00, 'color' => 'blue',   'is_active' => true],
            ['tier' => 'Medium',   'discount_percentage' => 5.00,  'color' => 'purple', 'is_active' => true],
            ['tier' => 'Small',    'discount_percentage' => 2.00,  'color' => 'gray',   'is_active' => true],
            ['tier' => 'New',      'discount_percentage' => 0.00,  'color' => 'green',  'is_active' => true],
        ];

        foreach ($defaults as $row) {
            TierDiscount::updateOrCreate(['tier' => $row['tier']], $row);
        }
    }
}
