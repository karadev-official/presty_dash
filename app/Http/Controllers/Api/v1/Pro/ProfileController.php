<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Image;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            // 'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }

        // if (isset($validatedData['specialty'])) {
        $user->email = $validatedData['email'];
        // }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'image_id' => ['required', 'exists:images,id'],
        ]);

        $user = $request->user();

        $image = Image::where('id', $request->image_id)
            ->where('user_id', $user->id) // sécurité
            ->firstOrFail();

        $user->update([
            'avatar_image_id' => $image->id,
        ]);

        return response()->json([
            'user' => $this->payload($user),
        ]);
    }

    public function payload($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'role' => $user->roles->first()?->name ?? null,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
