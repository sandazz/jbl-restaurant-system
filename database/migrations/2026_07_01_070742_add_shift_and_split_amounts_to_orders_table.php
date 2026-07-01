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
            $table->foreignId('shift_id')->nullable()->after('user_id')
                ->constrained('clerk_balancings')->nullOnDelete();
            $table->decimal('cash_amount', 12, 2)->nullable()->after('amount_paid');
            $table->decimal('card_amount', 12, 2)->nullable()->after('cash_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
            $table->dropColumn(['cash_amount', 'card_amount']);
        });
    }
};
