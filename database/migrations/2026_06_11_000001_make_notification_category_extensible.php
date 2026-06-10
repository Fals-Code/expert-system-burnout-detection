<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert the legacy ENUM column into VARCHAR so new, validated
     * notification categories do not fail with MySQL data truncation.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'category')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(
                "ALTER TABLE `notifications` MODIFY `category` VARCHAR(32) NOT NULL DEFAULT 'informasi'"
            );
        }
    }

    /**
     * Restore the original categories when rolling back.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'category')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::table('notifications')
                ->whereNotIn('category', ['informasi', 'peringatan', 'pengingat'])
                ->update(['category' => 'informasi']);

            DB::statement(
                "ALTER TABLE `notifications` MODIFY `category` ENUM('informasi','peringatan','pengingat') NOT NULL DEFAULT 'informasi'"
            );
        }
    }
};
