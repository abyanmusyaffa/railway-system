<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingMonitorController extends Controller
{
    public function index()
    {
        return Inertia::render('Officer/Monitor', [
            'bookings' => DB::table('v_confirmed_bookings')->get(),
        ]);
    }

    public function complete(Booking $booking)
    {
        // Update ini otomatis memicu trg_after_booking_status_update
        $booking->update(['booking_status' => 'COMPLETED']);
        return back()->with('success', 'Booking ditandai selesai.');
    }
}
