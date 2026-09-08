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
        Schema::create('route_stop_bids', function (Blueprint $table) {
            $table->foreignId('route_stop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rest_stop_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2);

            $table->primary(['route_stop_id', 'rest_stop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stop_bids');
    }
};
