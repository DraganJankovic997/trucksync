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
        Schema::create('driver_route', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_convoy_leader')->default(false);
            $table->timestamps();

            $table->unique(['driver_id', 'route_id']);
            $table->index('route_id');
        });

        DB::statement(
            'CREATE UNIQUE INDEX driver_route_route_id_convoy_leader_unique ON driver_route (route_id) WHERE is_convoy_leader = true'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS driver_route_route_id_convoy_leader_unique');

        Schema::dropIfExists('driver_route');
    }
};
