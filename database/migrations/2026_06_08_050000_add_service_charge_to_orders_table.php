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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('service_charge_enabled')->default(true)->after('subtotal');
            $table->decimal('service_charge_rate', 5, 2)->default(8.00)->after('service_charge_enabled');
            $table->decimal('service_charge_amount', 12, 2)->default(0)->after('service_charge_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['service_charge_enabled', 'service_charge_rate', 'service_charge_amount']);
        });
    }
};
