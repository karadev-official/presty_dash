<?php

use App\Http\Controllers\Api\v1\AuthController;
use Faker\Factory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        $faker = Factory::create();
        $user = new User();
        $user->name = $faker->name();
        $user->email = $faker->unique()->safeEmail();
        $user->password = Hash::make('password');
        $user->save();
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/users', function (Request $request) {
            $users = User::all();
            return response()->json([
                'users' => $users,
            ]);
        });
    });

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});
