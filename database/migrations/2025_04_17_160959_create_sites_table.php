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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->string('site_img');
            $table->string('location');
            $table->string('value');
            $table->string('duration');
            $table->string('settled_amnt');
            $table->string('pending_amnt');
            $table->string('expense')->nullable();
            $table->string('status')->default('New')->nullable();
            $table->boolean('is_inactive')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
