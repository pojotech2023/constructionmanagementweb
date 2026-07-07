<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE sites MODIFY site_img VARCHAR(255) NULL');
        DB::statement('ALTER TABLE sites MODIFY location VARCHAR(255) NULL');
        DB::statement('ALTER TABLE sites MODIFY duration VARCHAR(255) NULL');

        DB::statement('ALTER TABLE customers MODIFY name VARCHAR(255) NULL');
        DB::statement('ALTER TABLE customers MODIFY mobile_no VARCHAR(255) NULL');
        DB::statement('ALTER TABLE customers MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE customers MODIFY dob DATE NULL');
        DB::statement('ALTER TABLE customers MODIFY address VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE sites MODIFY site_img VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE sites MODIFY location VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE sites MODIFY duration VARCHAR(255) NOT NULL");

        DB::statement("ALTER TABLE customers MODIFY name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE customers MODIFY mobile_no VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE customers MODIFY email VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE customers MODIFY dob DATE NOT NULL");
        DB::statement("ALTER TABLE customers MODIFY address VARCHAR(255) NOT NULL");
    }
};
