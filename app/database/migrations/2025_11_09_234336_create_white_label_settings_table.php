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
        Schema::create('white_label_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, file, color, etc
            $table->timestamps();
        });

        // Inserir configurações padrão
        DB::table('white_label_settings')->insert([
            ['key' => 'favicon', 'value' => null, 'type' => 'file', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_color', 'value' => '#D4AF37', 'type' => 'color', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'dashboard_banner', 'value' => null, 'type' => 'file', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('white_label_settings');
    }
};
