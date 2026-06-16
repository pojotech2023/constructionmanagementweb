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
        Schema::create('default_items', function (Blueprint $table) {
        $table->id();
        $table->string('particular');
        $table->decimal('rate', 12, 2);
        $table->decimal('sqFt', 12, 2);
        $table->string('unit');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('default_items');
    }
};
