<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'specialty' => 'sometimes|string|max:255',
            // 'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }

        if (isset($validatedData['speciality'])) {
            $user->speciality = $validatedData['speciality]'];
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    public function payload($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'specialty' => $user->specialty,
            'email' => $user->email,
            'role' => $user->roles->first()?->name ?? null,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
