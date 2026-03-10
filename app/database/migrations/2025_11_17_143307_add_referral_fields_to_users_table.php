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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('referrer_id')->nullable()->after('id');
            $table->string('referral_code', 20)->unique()->nullable()->after('referrer_id');
            $table->decimal('commission_percentage', 5, 2)->default(1.00)->after('referral_code');
            $table->decimal('commission_fixed', 10, 2)->default(0.00)->after('commission_percentage');
            
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('set null');
            $table->index('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropIndex(['referral_code']);
            $table->dropColumn(['referrer_id', 'referral_code', 'commission_percentage', 'commission_fixed']);
        });
    }
};
