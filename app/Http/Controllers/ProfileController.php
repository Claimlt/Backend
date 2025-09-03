<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\returnArgument;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function profile()
    {
        $user = Auth::user();

        return new ProfileResource($user->load('avatar'));
    }
    public function getAllProfile()
    {
        $users = User::with('avatar')->get();
        return ProfileResource::collection($users);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => "required|string|digits:10",
            'avatar' => 'sometimes|uuid|exists:image,id',
        ]);

        $user = Auth::user();

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'contact_number' => $request->contact_number,
        ]);

        Image::where('id', $request->input('avatar'))
            ->update([
                'imageable_id' => $user->id,
                'imageable_type' => User::class,
            ]);

        return new ProfileResource($user->load('avatar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|uuid|exists:image,id',
        ]);

        $user = Auth::user();

        Image::where('id', $request->input('avatar'))
            ->update([
                'imageable_id' => $user->id,
                'imageable_type' => User::class,
            ]);
        return new ProfileResource($user->load('avatar'));
    }

}
