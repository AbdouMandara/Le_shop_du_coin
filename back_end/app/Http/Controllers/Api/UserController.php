<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->has('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('label', $request->role);
            });
        }

        return UserResource::collection($query->latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validatedData['password']),
            'role_id' => $validatedData['role_id'],
        ]);

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
