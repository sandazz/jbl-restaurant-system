<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name');
            $table->enum('tier', ['VIP', 'Moderate', 'Medium', 'Small', 'New'])->default('New')->after('status');
            $table->string('slmc_registration_number')->nullable()->after('tier');
            $table->boolean('print_tier_on_receipt')->default(false)->after('slmc_registration_number');
            $table->index('tier');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['tier']);
            $table->dropColumn(['title', 'tier', 'slmc_registration_number', 'print_tier_on_receipt']);
        });
    }
};
