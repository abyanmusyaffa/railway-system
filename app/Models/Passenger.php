<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\PassengerDetail;
use App\Models\PassengerTravelLog;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Passenger extends Model
{
    protected $primaryKey = 'passenger_id';
    public $timestamps = false;
    protected $fillable = ['identity_number','full_name','email','phone_number','passenger_type'];

    public function detail(): HasOne
    {
        return $this->hasOne(PassengerDetail::class, 'passenger_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'passenger_id');
    }

    // N:M ke schedules lewat tabel bookings
    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'bookings', 'passenger_id', 'schedule_id')
            ->withPivot(['booking_id','booking_code','seat_number','booking_status','total_paid']);
    }

    public function travelLogs(): HasMany
    {
        return $this->hasMany(PassengerTravelLog::class, 'passenger_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'passenger_id', 'passenger_id');
    }
}
