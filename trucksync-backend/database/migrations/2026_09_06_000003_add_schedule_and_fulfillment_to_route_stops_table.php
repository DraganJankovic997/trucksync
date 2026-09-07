<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->dateTime('stop_at')->nullable()->after('route_id');
            $table->dateTime('fulfiled_at')->nullable()->after('stop_at');
            $table->foreignId('fulfiled_by')
                ->nullable()
                ->after('fulfiled_at')
                ->constrained('rest_stops')
                ->nullOnDelete();
        });

        DB::table('route_stops')
            ->whereNull('stop_at')
            ->update(['stop_at' => now()->addDay()]);

        Schema::table('route_stops', function (Blueprint $table) {
            $table->dateTime('stop_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fulfiled_by');
            $table->dropColumn(['stop_at', 'fulfiled_at']);
        });
    }
};
