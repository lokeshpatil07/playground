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
        // Convert existing strings to JSON format
        DB::statement("UPDATE turfs SET sport_type = CONCAT('[\"', sport_type, '\"]') WHERE sport_type NOT LIKE '[%'");

        Schema::table('turfs', function (Blueprint $table) {
            $table->json('sport_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turfs', function (Blueprint $table) {
            $table->string('sport_type')->change();
        });
    }
};
