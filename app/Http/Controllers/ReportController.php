<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $totalExecutiveTrains = DB::selectOne('SELECT fn_get_total_executive_trains() AS total')->total;

        $revenuePerSchedule = DB::table('bookings')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.schedule_id')
            ->join('trains', 'schedules.train_id', '=', 'trains.train_id')
            ->select('schedules.schedule_id', 'trains.train_name', DB::raw('SUM(bookings.total_paid) as revenue'), DB::raw('COUNT(*) as total_bookings'))
            ->where('bookings.booking_status', '!=', 'CANCELLED')
            ->groupBy('schedules.schedule_id', 'trains.train_name')
            ->orderByDesc('revenue')
            ->get();

        $occupancy = DB::table('schedules')
            ->join('trains', 'schedules.train_id', '=', 'trains.train_id')
            ->leftJoin('bookings', function ($join) {
                $join->on('schedules.schedule_id', '=', 'bookings.schedule_id')
                     ->whereIn('bookings.booking_status', ['CONFIRMED', 'COMPLETED']);
            })
            ->select('schedules.schedule_id', 'trains.train_name', 'trains.total_seats', DB::raw('COUNT(bookings.booking_id) as booked_seats'))
            ->groupBy('schedules.schedule_id', 'trains.train_name', 'trains.total_seats')
            ->get();

        return Inertia::render('Admin/Report', [
            'totalExecutiveTrains' => $totalExecutiveTrains,
            'revenuePerSchedule'   => $revenuePerSchedule,
            'occupancy'            => $occupancy,
        ]);
    }
}
