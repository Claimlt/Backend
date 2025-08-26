<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserDetailsHandleRequest;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Auth;

class UserDetailsController extends Controller
{
    /**
     * Get logged-in user details
     */
    public function show()
    {
        $user = Auth::user();
        $details = UserDetails::where('user_id', $user->id)->first();

        return response()->json([
            'user_details' => $details,
        ], 200);
    }

    /**
     * Update or create user details
     */
    public function updateDetails(UserDetailsHandleRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $userDetails = UserDetails::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response()->json([
            'message' => 'User details updated successfully',
            'user_details' => $userDetails
        ], 200);
    }
}
