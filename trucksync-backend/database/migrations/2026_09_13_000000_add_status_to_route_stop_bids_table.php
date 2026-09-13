<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_STATUS = 'pending';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('route_stop_bids', function (Blueprint $table) {
            $table->string('status')->default(self::DEFAULT_STATUS);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_stop_bids', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropColumn('status');
        });
    }
};
