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
         Schema::table('ticket_messages', function (Blueprint $table) {
        $table->unsignedBigInteger('site_id')->nullable()->after('ticket_id');
        $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            //
        });
    }
};
