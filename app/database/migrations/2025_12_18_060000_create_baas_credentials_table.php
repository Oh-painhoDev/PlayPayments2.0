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
        Schema::create('baas_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->unique();
            $table->text('public_key')->nullable();
            $table->text('secret_key');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sandbox')->default(true);
            $table->boolean('is_default')->default(false);
            $table->decimal('withdrawal_fee', 10, 2)->default(0.00);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baas_credentials');
    }
};

