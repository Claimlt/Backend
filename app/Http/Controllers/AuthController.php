<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthReqest;
use App\Http\Requests\LoginReqest;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //register function
    public function register(AuthReqest $request)
    {
        $request->validated();

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_number' => $request->contact_number,
            'nic' => $request->nic,
            'role' => 'user',
            'status' => 'pending',
        ]);
            UserDetails::create([
            'user_id'    => $user->id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'province'   => null,
            'district'   => null,
            'city'       => null,
            'address'    => null,
            'profile_image' => null,
        ]);
        event(new Registered($user));

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }
    //login function
    public function login(LoginReqest $request)
    {
        $request->validated();

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        $userDetails = UserDetails::where('user_id', $user->id)->first();

        $isDetailsCompleted = $userDetails &&
            $userDetails->province &&
            $userDetails->district &&
            $userDetails->city &&
            $userDetails->address;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'user_details' => $userDetails,
            'show_details_modal' => !$isDetailsCompleted,
        ], 200);
    }
    //logout function
    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);

    }
}

