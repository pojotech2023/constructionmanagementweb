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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
        $table->unsignedBigInteger('site_id');
        $table->unsignedBigInteger('customer_id');
        $table->text('ticket');
        $table->timestamps();

        // Add foreign keys if needed
        $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
