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
        Schema::create('rest_stop_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rest_stop_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('works_from')->nullable();
            $table->time('works_to')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['rest_stop_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rest_stop_working_hours');
    }
};
