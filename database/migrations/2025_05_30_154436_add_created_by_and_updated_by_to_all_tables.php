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
        $tables = [
            'agents',
            'attendances',
            'customers',
            'device_tokens',
            'material_orders',
            'material_payments',
            'material_requests',
            'other_utilities',
            'other_utilities_subs',
            'properties',
            'quotation_details',
            'quotations',
            'sites',
            'subcontractor_payments',
            'subcontractors',
            'users',
            'vendor_pay_details',
            'vendor_payments',
            'vendors',
            'wages'
        ];
        foreach ($tables as $table) {
            Schema::table($table, function ($t) use ($table) {
                if (!Schema::hasColumn($table, 'created_by')) {
                    $t->unsignedBigInteger('created_by')->nullable()->before('created_at');
                }
                if (!Schema::hasColumn($table, 'updated_by')) {
                    $t->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('all_tables', function (Blueprint $table) {
            //
        });
    }
};
