<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\PassengerTravelLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GateScanController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $passengers = collect();

        if ($query !== '') {
            $passengers = Passenger::query()
                ->where('identity_number', 'like', "%{$query}%")
                ->orWhere('full_name', 'like', "%{$query}%")
                ->with(['bookings' => function ($q) {
                    $q->whereIn('booking_status', ['CONFIRMED', 'COMPLETED'])
                      ->with('schedule.train')
                      ->orderByDesc('booking_id');
                }])
                ->limit(10)
                ->get();
        }

        return Inertia::render('Officer/GateScan', [
            'query'      => $query,
            'passengers' => $passengers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'passenger_id' => 'required|integer|exists:passengers,passenger_id',
            'action_type'  => 'required|in:GATE_IN,GATE_OUT,BOARDING',
            'gate_number'  => 'required|string|max:10',
        ]);

        PassengerTravelLog::create([...$validated, 'scanned_at' => now()]);

        return back()->with('success', 'Scan tercatat.');
    }
}
