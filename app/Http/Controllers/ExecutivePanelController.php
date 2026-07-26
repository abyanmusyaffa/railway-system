<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExecutivePanelController extends Controller
{
    public function index()
    {
        return Inertia::render('Officer/Executive', [
            'bookings' => DB::table('v_executive_confirmed_bookings')->get(),
        ]);
    }
}
