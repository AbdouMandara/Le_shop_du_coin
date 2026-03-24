<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user->load('role')),
        ])->cookie('auth_token', $token, 60 * 24, null, null, false, true, false, 'Lax');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // Check if currentAccessToken exists and has a delete method (not a TransientToken)
            if ($user->currentAccessToken() && method_exists($user->currentAccessToken(), 'delete')) {
                $user->currentAccessToken()->delete();
            }

            // Standard web logout if using sessions
            Auth::guard('web')->logout();
            
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ])->cookie(cookie()->forget('auth_token'));
    }
}
