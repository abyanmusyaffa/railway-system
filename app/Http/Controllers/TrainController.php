<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Trains/Index', ['trains' => Train::orderBy('train_id')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'train_code'  => 'required|string|max:20|unique:trains',
            'train_name'  => 'required|string|max:100',
            'train_class' => 'required|in:EXECUTIVE,BUSINESS,ECONOMY',
            'total_seats' => 'required|integer|min:1',
        ]);
        Train::create($validated);
        return back()->with('success', 'Kereta ditambahkan.');
    }

    public function update(Request $request, Train $train)
    {
        $validated = $request->validate([
            'train_code'  => 'required|string|max:20|unique:trains,train_code,' . $train->train_id . ',train_id',
            'train_name'  => 'required|string|max:100',
            'train_class' => 'required|in:EXECUTIVE,BUSINESS,ECONOMY',
            'total_seats' => 'required|integer|min:1',
        ]);
        $train->update($validated);
        return back()->with('success', 'Kereta diperbarui.');
    }

    public function destroy(Train $train)
    {
        $train->delete();
        return back()->with('success', 'Kereta dihapus.');
    }
}
