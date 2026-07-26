<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use App\Models\PassengerDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $passenger = Passenger::with('detail')->findOrFail($request->user()->passenger_id);
        return Inertia::render('Passenger/Profile', ['passenger' => $passenger]);
    }

    public function update(Request $request)
    {
        $passenger = Passenger::findOrFail($request->user()->passenger_id);

        $validated = $request->validate([
            'full_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:100|unique:passengers,email,' . $passenger->passenger_id . ',passenger_id',
            'phone_number' => 'nullable|string|max:20',
            'address'                 => 'nullable|string|max:255',
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $passenger->update([
            'full_name'    => $validated['full_name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'],
        ]);

        PassengerDetail::updateOrCreate(
            ['passenger_id' => $passenger->passenger_id],
            [
                'address'                 => $validated['address'] ?? null,
                'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]
        );

        // Sinkronkan nama & email ke akun login juga
        $request->user()->update([
            'name'  => $validated['full_name'],
            'email' => $validated['email'],
        ]);

        return back()->with('success', 'Profil diperbarui.');
    }
}
