<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Train;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AScheduleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Schedules/Index', [
            'schedules' => Schedule::with('train')->orderBy('departure_time')->get(),
            'trains'    => Train::orderBy('train_name')->get(['train_id', 'train_name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'train_id'            => 'required|exists:trains,train_id',
            'origin_station'      => 'required|string|max:100',
            'destination_station' => 'required|string|max:100',
            'departure_time'      => 'required|date',
            'arrival_time'        => 'required|date|after:departure_time',
            'base_ticket_price'   => 'required|numeric|min:0',
        ]);
        Schedule::create($validated);
        return back()->with('success', 'Jadwal ditambahkan.');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'train_id'            => 'required|exists:trains,train_id',
            'origin_station'      => 'required|string|max:100',
            'destination_station' => 'required|string|max:100',
            'departure_time'      => 'required|date',
            'arrival_time'        => 'required|date|after:departure_time',
            'base_ticket_price'   => 'required|numeric|min:0',
        ]);
        $schedule->update($validated);
        return back()->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Jadwal dihapus.');
    }
}
