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
        Schema::create('barangay_ira_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->decimal('ira_share', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure one record per barangay per year
            $table->unique(['barangay_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangay_ira_shares');
    }
};

