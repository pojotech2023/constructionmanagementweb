<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE vendors MODIFY site_utilities VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vendors MODIFY mobile_no VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vendors MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vendors MODIFY address VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vendors MODIFY gst VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE vendors MODIFY site_utilities VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vendors MODIFY mobile_no VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vendors MODIFY email VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vendors MODIFY address VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vendors MODIFY gst VARCHAR(255) NOT NULL');
    }
};
