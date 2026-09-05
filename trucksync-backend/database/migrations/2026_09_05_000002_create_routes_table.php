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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatcher_id')->constrained()->cascadeOnDelete();
            $table->text('origin');
            $table->text('destination');
            $table->text('planned_travel_details')->nullable();
            $table->unsignedInteger('convoy_size');
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
