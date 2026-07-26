<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $schedules = Schedule::with('train')
            ->orderBy('departure_time')
            ->get()
            ->map(function (Schedule $s) {
                $booked = Booking::where('schedule_id', $s->schedule_id)
                    ->whereIn('booking_status', ['CONFIRMED', 'COMPLETED'])
                    ->count();

                return [
                    'schedule_id'         => $s->schedule_id,
                    'train_name'          => $s->train->train_name,
                    'train_class'         => $s->train->train_class,
                    'origin_station'      => $s->origin_station,
                    'destination_station' => $s->destination_station,
                    'departure_time'      => $s->departure_time,
                    'base_ticket_price'   => $s->base_ticket_price,
                    'seats_available'     => $s->train->total_seats - $booked,
                ];
            });

        return Inertia::render('Booking/Dashboard', ['schedules' => $schedules]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|integer|exists:schedules,schedule_id',
        ]);

        // Diambil dari sesi login, BUKAN dari input form
        $passengerId = $request->user()->passenger_id;
        abort_unless($passengerId, 422, 'Akun Anda belum terhubung dengan data penumpang.');

        try {
            DB::statement('CALL sp_book_ticket(?, ?)', [$passengerId, $validated['schedule_id']]);
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors(['booking' => 'Gagal booking: kursi mungkin sudah penuh.']);
        }

        return redirect()->route('passenger.history')->with('success', 'Tiket berhasil dipesan.');
    }
}
