<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Override the username method to use 'Username' instead of 'email'
    public function username()
    {
        return 'Username';
    }

    // Override the credentials method to use correct column names
    protected function credentials(Request $request)
    {
        return [
            'Username' => $request->Username,
            'Password' => $request->Password,
            'IsDeleted' => false
        ];
    }

    // Override the validateLogin method to use correct column names
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'Username' => 'required|string',
            'Password' => 'required|string',
        ]);
    }
} 