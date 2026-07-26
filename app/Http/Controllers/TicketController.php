<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function show(Booking $booking, Request $request)
    {
        abort_unless($booking->passenger_id === $request->user()->passenger_id, 403);

        return Inertia::render('Passenger/Ticket', [
            'booking' => $booking->load('schedule.train', 'passenger'),
        ]);
    }
}
