<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clerk_balancings', function (Blueprint $table) {
            $table->decimal('opening_amount', 12, 2)->default(0)->after('shift_start');
        });
    }

    public function down(): void
    {
        Schema::table('clerk_balancings', function (Blueprint $table) {
            $table->dropColumn('opening_amount');
        });
    }
};
