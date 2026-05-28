<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite cannot ALTER a column or drop a CHECK constraint — must recreate the table.
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('
                CREATE TABLE tier_discounts_new (
                    id               INTEGER PRIMARY KEY AUTOINCREMENT,
                    tier             VARCHAR(100) NOT NULL,
                    discount_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
                    color            VARCHAR(30)  NOT NULL DEFAULT \'blue\',
                    is_active        TINYINT(1)   NOT NULL DEFAULT 1,
                    created_at       DATETIME     NULL,
                    updated_at       DATETIME     NULL,
                    UNIQUE(tier)
                )
            ');
            DB::statement('INSERT INTO tier_discounts_new SELECT * FROM tier_discounts');
            DB::statement('DROP TABLE tier_discounts');
            DB::statement('ALTER TABLE tier_discounts_new RENAME TO tier_discounts');
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE tier_discounts MODIFY COLUMN tier VARCHAR(100) NOT NULL');
        }
    }

    public function down(): void
    {
        // Intentionally left without reversal — restoring an ENUM would reject existing custom tiers.
    }
};
