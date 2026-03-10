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
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable(); // Null = cliente escolhe o valor
            $table->string('currency', 3)->default('BRL');
            $table->enum('payment_method', ['pix', 'credit_card', 'bank_slip', 'all'])->default('all');
            $table->boolean('allow_custom_amount')->default(false);
            $table->decimal('min_amount', 12, 2)->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_uses')->nullable(); // Null = ilimitado
            $table->integer('current_uses')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_links');
    }
};
