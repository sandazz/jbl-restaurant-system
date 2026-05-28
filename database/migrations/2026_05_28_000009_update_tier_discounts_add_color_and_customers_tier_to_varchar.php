<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a badge color to each tier so custom tiers can be styled
        Schema::table('tier_discounts', function (Blueprint $table) {
            $table->string('color', 30)->default('blue')->after('discount_percentage');
        });

        // MySQL: relax the ENUM so any custom tier name can be stored
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE customers MODIFY COLUMN tier VARCHAR(100) NOT NULL DEFAULT 'New'");
        }
    }

    public function down(): void
    {
        Schema::table('tier_discounts', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE customers MODIFY COLUMN tier ENUM('VIP','Moderate','Medium','Small','New') NOT NULL DEFAULT 'New'");
        }
    }
};
