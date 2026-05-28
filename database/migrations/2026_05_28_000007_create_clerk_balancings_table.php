<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clerk_balancings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('shift_start')->nullable();
            $table->timestamp('shift_end')->nullable();

            // Currency note counts
            $table->unsignedInteger('note_5000')->default(0);
            $table->unsignedInteger('note_2000')->default(0);
            $table->unsignedInteger('note_1000')->default(0);
            $table->unsignedInteger('note_500')->default(0);
            $table->unsignedInteger('note_200')->default(0);
            $table->unsignedInteger('note_100')->default(0);
            $table->unsignedInteger('note_50')->default(0);
            $table->unsignedInteger('note_20')->default(0);
            $table->unsignedInteger('note_10')->default(0);

            // Coin counts
            $table->unsignedInteger('coin_5')->default(0);
            $table->unsignedInteger('coin_2')->default(0);
            $table->unsignedInteger('coin_1')->default(0);
            $table->unsignedInteger('coin_50c')->default(0); // 50 cents

            // Computed totals stored at close time
            $table->decimal('physical_cash_total', 12, 2)->default(0);
            $table->decimal('expected_cash_total', 12, 2)->default(0);
            $table->decimal('expected_card_total', 12, 2)->default(0);
            $table->decimal('variance', 12, 2)->default(0); // absolute value
            $table->enum('variance_type', ['shortage', 'excess', 'balanced'])->default('balanced');

            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clerk_balancings');
    }
};
