<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'Username' => ['required', 'string', 'max:255', 'unique:UserAccount'],
            'Password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'Username' => $request->Username,
                'Password' => Hash::make($request->Password),
                'DateCreated' => Carbon::now(),
                'CreatedById' => 1, // You might want to adjust this based on your needs
                'IsDeleted' => false
            ]);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Registration successful!');

        } catch (\Exception $e) {
            DB::rollback();
            // Log the error for debugging
            \Log::error('Registration error: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}
