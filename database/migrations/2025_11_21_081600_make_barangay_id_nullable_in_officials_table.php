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

        // Use DB facade to modify the column (required for changing foreign key columns)
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `officials` MODIFY `barangay_id` BIGINT UNSIGNED NULL');

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

        // Use DB facade to modify the column back to NOT NULL
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `officials` MODIFY `barangay_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('officials', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('barangay_id')->references('id')->on('barangays')->onDelete('cascade');
        });
    }
};

