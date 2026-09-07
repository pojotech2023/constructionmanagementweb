<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Unit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['Meter', 'Roll'] as $unitName) {
            Unit::firstOrCreate(['name' => $unitName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Unit::whereIn('name', ['Meter', 'Roll'])->delete();
    }
};
