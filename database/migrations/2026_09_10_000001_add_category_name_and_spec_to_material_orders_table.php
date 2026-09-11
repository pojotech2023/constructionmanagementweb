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
        Schema::table('material_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('material_orders', 'category_name')) {
                $table->string('category_name')->nullable()->after('material_type');
            }
            if (!Schema::hasColumn('material_orders', 'spec')) {
                $table->string('spec')->nullable()->after('category_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_orders', function (Blueprint $table) {
            if (Schema::hasColumn('material_orders', 'spec')) {
                $table->dropColumn('spec');
            }
            if (Schema::hasColumn('material_orders', 'category_name')) {
                $table->dropColumn('category_name');
            }
        });
    }
};

