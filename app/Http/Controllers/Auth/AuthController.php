<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserAccount;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'Username' => 'required',
            'Password' => 'required',
        ]);

        $user = UserAccount::where('Username', $credentials['Username'])
            ->where('IsDeleted', false)
            ->first();

        if (!$user) {
            return back()->withErrors(['Username' => 'User not found'])->onlyInput('Username');
        }

        if (Hash::check($credentials['Password'], $user->Password) || 
            $credentials['Password'] === $user->Password) {
            
            // Update plain password to hash if needed
            if ($credentials['Password'] === $user->Password) {
                $user->Password = Hash::make($credentials['Password']);
                $user->save();
            }

            // Start session and login
            $request->session()->regenerate();
            Auth::login($user);

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['Password' => 'Invalid password'])->onlyInput('Username');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'Username' => 'required|unique:useraccount,Username',
            'Password' => 'required|min:8|confirmed',
            'Email' => 'required|email|unique:employee,Email',
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'Gender' => 'required|in:Male,Female',
            'Address' => 'required|string',
            'Role' => 'required|in:Admin,Employee'
        ]);

        DB::beginTransaction();
        
        try {
            // Create user account
            $user = UserAccount::create([
                'Username' => $validated['Username'],
                'Password' => Hash::make($validated['Password']),
                'DateCreated' => now(),
                'IsDeleted' => false
            ]);

            // Create employee record
            $employee = Employee::create([
                'UserAccountID' => $user->UserAccountID,
                'FirstName' => $validated['FirstName'],
                'LastName' => $validated['LastName'],
                'Email' => $validated['Email'],
                'Gender' => $validated['Gender'],
                'Address' => $validated['Address'],
                'Role' => $validated['Role'],
                'DateCreated' => now(),
                'CreatedByID' => null,
                'IsDeleted' => false
            ]);

            DB::commit();
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Registration successful!');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Registration failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/inventory/login')->with('success', 'You have been logged out successfully.');
        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());
            return redirect('/inventory/login');
        }
    }
}
