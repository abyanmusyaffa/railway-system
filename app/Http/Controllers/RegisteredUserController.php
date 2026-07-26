<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'email'                => 'required|string|email|max:100|unique:users',
            'password'             => ['required', 'confirmed', 'min:3'],
            'identity_number'      => 'required|string|max:30|unique:passengers',
            'phone_number'         => 'nullable|string|max:20',
        ]);

        // Setiap user baru otomatis jadi PASSENGER + dibuatkan record passengers
        $passenger = Passenger::create([
            'identity_number' => $validated['identity_number'],
            'full_name'       => $validated['name'],
            'email'           => $validated['email'],
            'phone_number'    => $validated['phone_number'] ?? null,
            'passenger_type'  => 'ADULT',
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => 'PASSENGER',
            'passenger_id'  => $passenger->passenger_id,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('booking.index');
    }
}
