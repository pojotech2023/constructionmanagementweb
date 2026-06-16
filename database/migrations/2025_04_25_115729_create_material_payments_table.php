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
        Schema::create('material_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('material_type');
            $table->date('date'); 
            $table->string('quantity'); 
            $table->integer('total_amount')->default(0);   
            $table->integer('settled_amount')->default(0);
            $table->integer('pending_amount')->default(0);
            $table->text('remarks')->nullable();
            $table->integer('status')->default(0);  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_payments');
    }
};
