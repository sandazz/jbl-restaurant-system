<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_discounts', function (Blueprint $table) {
            $table->id();
            $table->enum('tier', ['VIP', 'Moderate', 'Medium', 'Small', 'New'])->unique();
            $table->decimal('discount_percentage', 5, 2)->default(0); // 0.00 – 100.00
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_discounts');
    }
};
