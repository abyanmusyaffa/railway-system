<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users/Index', ['users' => User::orderBy('id')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate(['role' => 'required|in:ADMIN,OFFICER,PASSENGER']);
        $user->update($validated);
        return back()->with('success', 'Role user diperbarui.');
    }
}
