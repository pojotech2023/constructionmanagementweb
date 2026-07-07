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
        Schema::create('sales_bill_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_bill_id')->constrained()->onDelete('cascade');
            $table->string('particular');
            $table->decimal('count', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_bill_details');
    }
};
