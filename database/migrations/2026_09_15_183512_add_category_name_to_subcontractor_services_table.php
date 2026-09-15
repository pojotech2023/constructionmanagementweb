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
        Schema::table('subcontractor_services', function (Blueprint $table) {
            $table->string('category_name')->nullable()->after('subcontractor_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subcontractor_services', function (Blueprint $table) {
            $table->dropColumn('category_name');
        });
    }
};
