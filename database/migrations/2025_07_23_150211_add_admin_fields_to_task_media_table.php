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
        Schema::table('task_media', function (Blueprint $table) {
        $table->text('admin_remark')->nullable();
        $table->enum('status', ['approved', 'rejected'])->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_media', function (Blueprint $table) {
            //
        });
    }
};
