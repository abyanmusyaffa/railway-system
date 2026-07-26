<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Train;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $primaryKey = 'schedule_id';
    public $timestamps = false;
    protected $fillable = ['train_id','origin_station','destination_station','departure_time','arrival_time','base_ticket_price'];

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class, 'train_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    public function passengers(): BelongsToMany
    {
        return $this->belongsToMany(Passenger::class, 'bookings', 'schedule_id', 'passenger_id')
            ->withPivot(['booking_id','booking_code','seat_number','booking_status','total_paid']);
    }
}
