<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    public function run()
    {
        DB::statement('CALL sp_archive_cancelled_bookings()');
        return back()->with('success', 'Booking yang dibatalkan telah diarsipkan.');
    }
}
