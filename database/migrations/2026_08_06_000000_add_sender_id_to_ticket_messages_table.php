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
            // Stores the id of the actual sender:
            // - users.id when sender_type is 'admin' or 'supervisor'
            // - customers.id when sender_type is 'client'
            $table->unsignedBigInteger('sender_id')->nullable()->after('sender_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropColumn('sender_id');
        });
    }
};
