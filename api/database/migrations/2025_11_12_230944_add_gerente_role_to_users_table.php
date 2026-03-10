<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'gerente'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'gerente') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before removing 'gerente', convert all gerente users to admin
        DB::table('users')
            ->where('role', 'gerente')
            ->update(['role' => 'admin']);
        
        // Revert the enum to original values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user'");
    }
};
