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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->foreignId('passenger_id')->constrained('passengers', 'passenger_id')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules', 'schedule_id')->cascadeOnDelete();
            $table->string('booking_code', 20)->unique();
            $table->string('seat_number', 10);
            $table->enum('booking_status', ['CONFIRMED', 'CANCELLED', 'COMPLETED'])->default('CONFIRMED');
            $table->decimal('total_paid', 12, 2);
            $table->index(['passenger_id', 'schedule_id'], 'idx_booking_passenger_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
