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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->foreignId('train_id')->constrained('trains', 'train_id')->cascadeOnDelete();
            $table->string('origin_station', 100);
            $table->string('destination_station', 100);
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->decimal('base_ticket_price', 12, 2);
            $table->index(['origin_station', 'destination_station'], 'idx_schedule_route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
