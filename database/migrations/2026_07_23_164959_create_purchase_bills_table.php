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
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('name');
            $table->string('mobile_no')->nullable();
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->date('date');
            $table->string('location')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('terms_conditions')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};
