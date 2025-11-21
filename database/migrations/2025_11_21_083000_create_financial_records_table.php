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
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique(); // Year of the financial record
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('ira_allocation', 15, 2)->default(0); // Internal Revenue Allotment
            $table->decimal('service_business_income', 15, 2)->default(0);
            $table->decimal('local_tax_collections', 15, 2)->default(0);
            $table->decimal('property_tax', 15, 2)->default(0);
            $table->decimal('goods_services_tax', 15, 2)->default(0);
            $table->decimal('total_expenditures', 15, 2)->default(0);
            $table->decimal('personnel_services', 15, 2)->default(0);
            $table->decimal('maintenance_operating_expenses', 15, 2)->default(0);
            $table->decimal('capital_outlay', 15, 2)->default(0);
            $table->decimal('fiscal_balance', 15, 2)->default(0); // Surplus/Deficit
            $table->decimal('total_assets', 15, 2)->default(0);
            $table->decimal('total_liabilities', 15, 2)->default(0);
            $table->decimal('net_equity', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_records');
    }
};

