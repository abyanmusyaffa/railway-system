<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('v_public_train_schedules');

        if ($request->filled('origin')) {
            $query->where('origin_station', $request->origin);
        }
        if ($request->filled('destination')) {
            $query->where('destination_station', $request->destination);
        }

        return Inertia::render('Public/Schedules', [
            'schedules' => $query->orderBy('departure_time')->get(),
            'filters'   => $request->only(['origin', 'destination']),
        ]);
    }
}
