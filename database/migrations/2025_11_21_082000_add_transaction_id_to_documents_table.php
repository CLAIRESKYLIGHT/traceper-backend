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
            // Drop existing foreign key constraint on project_id
            $table->dropForeign(['project_id']);
        });

        // Make project_id nullable (documents can be transaction-specific)
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `documents` MODIFY `project_id` BIGINT UNSIGNED NULL');

        Schema::table('documents', function (Blueprint $table) {
            // Re-add foreign key constraint (nullable)
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Add transaction_id (nullable - documents can be project-level or transaction-specific)
            $table->foreignId('transaction_id')
                ->nullable()
                ->after('project_id')
                ->constrained('transactions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop transaction_id foreign key
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
            
            // Drop project_id foreign key
            $table->dropForeign(['project_id']);
        });

        // Make project_id required again
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `documents` MODIFY `project_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('documents', function (Blueprint $table) {
            // Re-add project_id foreign key (required)
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }
};

