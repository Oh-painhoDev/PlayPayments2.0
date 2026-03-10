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
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id'); // Quem indicou
            $table->unsignedBigInteger('referred_id'); // Quem foi indicado
            $table->unsignedBigInteger('transaction_id')->nullable(); // Transação que gerou a comissão
            $table->decimal('amount', 15, 2); // Valor da transação
            $table->decimal('commission_amount', 15, 2); // Valor da comissão
            $table->decimal('commission_percentage', 5, 2); // % usado no cálculo
            $table->decimal('commission_fixed', 10, 2)->default(0.00); // Taxa fixa
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            
            $table->index(['referrer_id', 'status']);
            $table->index(['referred_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
