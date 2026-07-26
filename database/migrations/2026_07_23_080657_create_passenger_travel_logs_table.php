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
        Schema::create('passenger_travel_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('passenger_id')->constrained('passengers', 'passenger_id')->cascadeOnDelete();
            $table->enum('action_type', ['GATE_IN', 'GATE_OUT', 'BOARDING']);
            $table->string('gate_number', 10)->nullable();
            $table->dateTime('scanned_at')->useCurrent();
            $table->index(['passenger_id', 'scanned_at'], 'idx_log_passenger_scan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passenger_travel_logs');
    }
};
