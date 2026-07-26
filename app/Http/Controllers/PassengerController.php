<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PassengerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Passengers/Index', [
            'passengers' => Passenger::with(['detail', 'user'])->orderByDesc('passenger_id')->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'identity_number' => 'required|string|max:30|unique:passengers',
            'full_name'       => 'required|string|max:100',
            'email'           => 'required|email|max:100|unique:passengers|unique:users',
            'phone_number'    => 'nullable|string|max:20',
            'passenger_type'  => 'required|in:ADULT,INFANT',
        ]);

        DB::transaction(function () use ($validated) {
            $passenger = Passenger::create($validated);

            // Setiap penumpang baru otomatis dapat akun login role PASSENGER.
            // Password default = No. Identitas, wajib diganti saat login pertama.
            User::create([
                'name'         => $passenger->full_name,
                'email'        => $passenger->email,
                'password'     => Hash::make($passenger->identity_number),
                'role'         => 'PASSENGER',
                'passenger_id' => $passenger->passenger_id,
            ]);
        });

        return back()->with('success', 'Penumpang & akun login berhasil dibuat. Password default: No. Identitas.');
    }

    public function update(Request $request, Passenger $passenger)
    {
        $validated = $request->validate([
            'identity_number' => 'required|string|max:30|unique:passengers,identity_number,' . $passenger->passenger_id . ',passenger_id',
            'full_name'       => 'required|string|max:100',
            'email'           => 'required|email|max:100|unique:passengers,email,' . $passenger->passenger_id . ',passenger_id',
            'phone_number'    => 'nullable|string|max:20',
            'passenger_type'  => 'required|in:ADULT,INFANT',
        ]);

        DB::transaction(function () use ($passenger, $validated) {
            $passenger->update($validated);

            // Sinkronkan ke akun user terkait (jika ada)
            $passenger->user?->update([
                'name'  => $validated['full_name'],
                'email' => $validated['email'],
            ]);
        });

        return back()->with('success', 'Penumpang diperbarui.');
    }

    public function destroy(Passenger $passenger)
    {
        // user ikut terhapus lewat cascadeOnDelete di migration,
        // tapi dipanggil eksplisit juga supaya tidak bergantung pada driver DB
        $passenger->user?->delete();
        $passenger->delete();

        return back()->with('success', 'Penumpang & akun login terkait telah dihapus.');
    }
}
