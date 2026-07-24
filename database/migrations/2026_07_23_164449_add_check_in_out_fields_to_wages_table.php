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
        Schema::table('wages', function (Blueprint $table) {
            $table->time('check_in_time')->nullable()->after('date');
            $table->string('check_in_photo')->nullable()->after('check_in_time');
            $table->time('check_out_time')->nullable()->after('check_in_photo');
            $table->string('check_out_photo')->nullable()->after('check_out_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wages', function (Blueprint $table) {
            $table->dropColumn(['check_in_time', 'check_in_photo', 'check_out_time', 'check_out_photo']);
        });
    }
};
