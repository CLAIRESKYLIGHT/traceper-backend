<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            // Drop the existing foreign key constraint first
            $table->dropForeign(['barangay_id']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL syntax
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE officials ALTER COLUMN barangay_id DROP NOT NULL');
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            // MySQL / MariaDB syntax
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE officials MODIFY barangay_id BIGINT UNSIGNED NULL');
        } else {
            // Fallback for other drivers
            Schema::table('officials', function (Blueprint $table) {
                $table->unsignedBigInteger('barangay_id')->nullable()->change();
            });
        }

        Schema::table('officials', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('barangay_id')->references('id')->on('barangays')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['barangay_id']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE officials ALTER COLUMN barangay_id SET NOT NULL');
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE officials MODIFY barangay_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('officials', function (Blueprint $table) {
                $table->unsignedBigInteger('barangay_id')->nullable(false)->change();
            });
        }

        Schema::table('officials', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('barangay_id')->references('id')->on('barangays')->onDelete('cascade');
        });
    }
};

