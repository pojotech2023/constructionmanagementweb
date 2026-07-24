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
        Schema::create('purchase_bill_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_bill_id');
            $table->string('particular');
            $table->decimal('count', 10, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('purchase_bill_id')->references('id')->on('purchase_bills')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bill_details');
    }
};
