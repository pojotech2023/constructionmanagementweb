<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supervisor_id');
            $table->string('module');
            $table->string('action');
            $table->boolean('is_allowed')->default(false);
            $table->timestamps();

            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['supervisor_id', 'module', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_permissions');
    }
};
