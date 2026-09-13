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
        Schema::create('route_stop_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_stop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rest_stop_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('used_at');
            $table->unsignedTinyInteger('rating');
            $table->text('report')->nullable();
            $table->boolean('is_report')->default(false);
            $table->timestamps();

            $table->unique('route_stop_id');
            $table->index(['rest_stop_id', 'rating']);
        });

        DB::statement(
            'ALTER TABLE route_stop_usages ADD CONSTRAINT route_stop_usages_rating_check CHECK (rating BETWEEN 1 AND 5)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stop_usages');
    }
};
