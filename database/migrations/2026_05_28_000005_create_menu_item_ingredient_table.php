<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table linking menu items (products) to their raw material ingredients
     * with a Bill of Materials quantity per portion sold.
     */
    public function up(): void
    {
        Schema::create('menu_item_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_required', 12, 3); // e.g. 250.000 grams of meat per portion
            $table->timestamps();

            $table->unique(['product_id', 'inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_ingredient');
    }
};
