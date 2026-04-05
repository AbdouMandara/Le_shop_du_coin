<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Return the authenticated user's profile.
     */
    public function show(Request $request)
    {
        return response()->json(new \App\Http\Resources\UserResource($request->user()->load('role')));
    }

    /**
     * Update the authenticated user's profile.
     * Profile photo is optional.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name'                  => 'sometimes|required|string|max:30',
            'email'                 => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'current_password'      => 'required_with:password|string',
            'password'              => ['nullable', 'confirmed', Password::min(8)],
        ];

        $validated = $request->validate($rules);

        // If the user wants to change the password, verify the current one first
        if (!empty($validated['password'])) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message'  => 'Le mot de passe actuel est incorrect.',
                    'errors'   => ['current_password' => ['Le mot de passe actuel est incorrect.']],
                ], 422);
            }
            $user->password = Hash::make($validated['password']);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo = $path;
        }

        // Update simple fields
        if (isset($validated['name']))  $user->name  = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => new \App\Http\Resources\UserResource($user->load('role')),
        ]);
    }
}
