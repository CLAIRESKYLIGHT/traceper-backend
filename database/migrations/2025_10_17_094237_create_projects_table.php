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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->decimal('budget_allocated',15,2)->default(0);
            $table->decimal('amount_spent',15,2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('status',['Not Started','In Progress','Completed','Delayed','Cancelled'])->default('Not Started');
            $table->foreignId('barangay_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contractor_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
