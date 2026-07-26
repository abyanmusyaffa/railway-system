<?php

namespace App\Models;

use App\Models\Passenger;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $primaryKey = 'booking_id';
    public $timestamps = false;
    protected $fillable = ['passenger_id','schedule_id','booking_code','seat_number','booking_status','total_paid'];

    public function passenger(): BelongsTo 
    { 
        return $this->belongsTo(Passenger::class, 'passenger_id'); 
    }
    
    public function schedule(): BelongsTo 
    { 
        return $this->belongsTo(Schedule::class, 'schedule_id'); 
    }
}
