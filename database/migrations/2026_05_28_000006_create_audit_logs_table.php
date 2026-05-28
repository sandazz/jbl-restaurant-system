<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action_type', ['void', 'delete', 'discount', 'override', 'refund']);
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('authorizing_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks'); // mandatory — must not be nullable
            $table->json('meta')->nullable(); // optional snapshot of order state at time of action
            $table->timestamps();

            $table->index('order_id');
            $table->index('cashier_id');
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
