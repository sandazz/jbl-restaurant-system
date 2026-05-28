<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite stores enum columns as plain TEXT and does not enforce the
        // allowed values, so 'billing' already works without any DDL change.
        // MySQL/MariaDB require an explicit MODIFY COLUMN to extend the enum.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE restaurant_tables MODIFY COLUMN status ENUM('available','occupied','reserved','cleaning','billing') NOT NULL DEFAULT 'available'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE restaurant_tables SET status = 'available' WHERE status = 'billing'");
            DB::statement("ALTER TABLE restaurant_tables MODIFY COLUMN status ENUM('available','occupied','reserved','cleaning') NOT NULL DEFAULT 'available'");
        }
    }
};
