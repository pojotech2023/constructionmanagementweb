<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('material_orders', 'order_no')) {
                $table->string('order_no', 50)->nullable()->after('vendor_id')->index();
            }
            if (!Schema::hasColumn('material_orders', 'category')) {
                $table->string('category', 150)->nullable()->after('material_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_orders', function (Blueprint $table) {
            if (Schema::hasColumn('material_orders', 'order_no')) {
                $table->dropColumn('order_no');
            }
            if (Schema::hasColumn('material_orders', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};

