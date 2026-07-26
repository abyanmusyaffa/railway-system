<?php

namespace App\Models;

use App\Models\Passenger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassengerDetail extends Model
{
    protected $primaryKey = 'detail_id';
    public $timestamps = false;
    protected $fillable = ['passenger_id','address','emergency_contact_name','emergency_contact_phone'];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }
}
