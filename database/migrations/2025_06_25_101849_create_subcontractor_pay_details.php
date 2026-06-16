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
        Schema::create('subcontractor_pay_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');
            $table->string('opening_balance')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('balance_amount')->nullable();
            $table->string('paid_amount')->nullable();
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
        Schema::dropIfExists('subcontractor_pay_details');
    }
};
