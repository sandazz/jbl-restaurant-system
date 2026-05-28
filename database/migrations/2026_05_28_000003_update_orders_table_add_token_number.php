<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // token_number is used when order is saved on_hold without a physical table
            $table->string('token_number')->nullable()->after('order_number');
            $table->index('token_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['token_number']);
            $table->dropColumn('token_number');
        });
    }
};
