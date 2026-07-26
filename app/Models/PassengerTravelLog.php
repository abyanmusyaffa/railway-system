<?php

namespace App\Models;

use App\Models\Passenger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassengerTravelLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = false;
    protected $fillable = ['passenger_id','action_type','gate_number','scanned_at'];

    public function passenger(): BelongsTo 
    { 
        return $this->belongsTo(Passenger::class, 'passenger_id'); 
    }
}
