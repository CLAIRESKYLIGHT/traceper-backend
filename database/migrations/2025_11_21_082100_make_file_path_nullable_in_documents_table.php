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
        Schema::table('documents', function (Blueprint $table) {
            // Make file_path nullable to allow placeholder documents
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `documents` MODIFY `file_path` VARCHAR(255) NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Make file_path required again (only if all documents have files)
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `documents` MODIFY `file_path` VARCHAR(255) NOT NULL');
        });
    }
};

