<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('schedule.train')
            ->where('passenger_id', $request->user()->passenger_id)
            ->orderByDesc('booking_id')
            ->get();

        return Inertia::render('Passenger/History', ['bookings' => $bookings]);
    }

}
