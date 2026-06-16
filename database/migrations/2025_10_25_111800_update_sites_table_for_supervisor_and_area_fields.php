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
        Schema::table('sites', function (Blueprint $table) {
            // 🗑️ Remove old columns
            if (Schema::hasColumn('sites', 'value')) {
                $table->dropColumn('value');
            }
            if (Schema::hasColumn('sites', 'settled_amnt')) {
                $table->dropColumn('settled_amnt');
            }
            if (Schema::hasColumn('sites', 'pending_amnt')) {
                $table->dropColumn('pending_amnt');
            }
            if (Schema::hasColumn('sites', 'expense')) {
                $table->dropColumn('expense');
            }

            // ➕ Add new columns
            $table->string('flat_area')->nullable()->after('location');
            $table->string('built_up_area')->nullable()->after('flat_area');

            // ➕ Add supervisor foreign key
            $table->unsignedBigInteger('supervisor_id')->nullable()->after('built_up_area');
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Revert added columns
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['flat_area', 'built_up_area', 'supervisor_id']);

            // Recreate removed columns (optional if you ever rollback)
            $table->decimal('value', 15, 2)->nullable();
            $table->decimal('settled_amnt', 15, 2)->nullable();
            $table->decimal('pending_amnt', 15, 2)->nullable();
            $table->decimal('expense', 15, 2)->nullable();
        });
    }
};
