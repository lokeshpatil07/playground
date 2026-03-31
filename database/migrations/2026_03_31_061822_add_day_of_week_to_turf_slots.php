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
        Schema::table('turf_slots', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')->nullable()->after('turf_id')->comment('0=Sunday, 1=Monday, ..., 6=Saturday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turf_slots', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
        });
    }
};
