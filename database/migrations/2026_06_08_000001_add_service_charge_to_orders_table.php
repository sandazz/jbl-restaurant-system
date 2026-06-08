<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('service_charge_rate', 5, 2)->default(0)->after('tax_amount');
            $table->decimal('service_charge_amount', 12, 2)->default(0)->after('service_charge_rate');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['service_charge_rate', 'service_charge_amount']);
        });
    }
};
