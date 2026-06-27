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
        $module = \App\Models\Module::create([
            'name' => 'Order History',
            'description' => 'View and manage completed orders with full details',
            'route' => 'order-history.index',
            'icon' => 'history',
        ]);

        // Grant access to Cashier role
        $cashierRole = \App\Models\Role::where('name', 'Cashier')->first();
        if ($cashierRole) {
            \Illuminate\Support\Facades\DB::table('role_module')->insertOrIgnore([
                'role_id' => $cashierRole->id,
                'module_id' => $module->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Module::where('route', 'order-history.index')->delete();
    }
};
