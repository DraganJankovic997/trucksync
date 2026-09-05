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
        Schema::table('dispatchers', function (Blueprint $table) {
            $table->dropColumn('country');
        });

        Schema::table('rest_stops', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatchers', function (Blueprint $table) {
            $table->string('country')->nullable();
        });

        Schema::table('rest_stops', function (Blueprint $table) {
            $table->string('country')->nullable();
        });
    }
};
