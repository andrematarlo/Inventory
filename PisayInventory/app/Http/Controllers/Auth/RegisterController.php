<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'Username' => ['required', 'string', 'max:255', 'unique:UserAccount'],
            'Password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        try {
            DB::beginTransaction();

            $user = new User();
            $user->Username = $data['Username'];
            $user->Password = Hash::make($data['Password']);
            $user->DateCreated = now();
            $user->IsDeleted = false;
            $user->save();

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration error: ' . $e->getMessage());
            throw $e;
        }
    }
} 