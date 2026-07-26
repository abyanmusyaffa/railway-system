<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DiscountController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Discount', [
            'schedules' => Schedule::with('train')->get(),
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'schedule_id'   => 'required|exists:schedules,schedule_id',
            'discount_rate' => 'required|numeric|min:0|max:100',
        ]);

        $result = DB::selectOne(
            'SELECT fn_calculate_discounted_ticket(?, ?) AS final_price',
            [$validated['schedule_id'], $validated['discount_rate']]
        );

        return back()->with('final_price', $result->final_price);
    }
}
