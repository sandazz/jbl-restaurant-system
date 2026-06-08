<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \App\Models\Module::create([
            'name' => 'Order History',
            'description' => 'View and manage completed orders with full details',
            'route' => 'order-history.index',
            'icon' => 'history',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Module::where('route', 'order-history.index')->delete();
    }
};
