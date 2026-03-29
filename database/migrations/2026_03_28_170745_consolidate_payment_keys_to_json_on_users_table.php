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
            $table->dropColumn(['razorpay_key', 'razorpay_secret']);
            $table->json('payment_settings')->nullable()->after('venue_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payment_settings');
            $table->string('razorpay_key')->nullable()->after('venue_address');
            $table->string('razorpay_secret')->nullable()->after('razorpay_key');
        });
    }
};
