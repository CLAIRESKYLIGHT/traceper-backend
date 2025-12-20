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
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE documents ALTER COLUMN file_path DROP NOT NULL');
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                \Illuminate\Support\Facades.DB::statement('ALTER TABLE documents MODIFY file_path VARCHAR(255) NULL');
            } else {
                $table->string('file_path', 255)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Make file_path required again (only if all documents have files)
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE documents ALTER COLUMN file_path SET NOT NULL');
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE documents MODIFY file_path VARCHAR(255) NOT NULL');
            } else {
                $table->string('file_path', 255)->nullable(false)->change();
            }
        });
    }
};

