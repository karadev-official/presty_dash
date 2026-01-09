<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\ImageController;
use App\Http\Controllers\Api\v1\Pro\OptionGroupController;
use App\Http\Controllers\Api\v1\Pro\ServiceOptionGroupAttachController;
use App\Http\Controllers\Api\v1\Pro\ServiceCategoryController;
use App\Http\Controllers\Api\v1\Pro\ServiceController;
use App\Http\Controllers\Api\v1\UploadController;
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

        // asssign role
        $user->assignRole('customer');
        return response()->json([
            'user' => $user,
            'role' => $user->getRoleNames(),
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

        // CategoriesService routes
        Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
        Route::get('/service-categories/{category}', [ServiceCategoryController::class, 'show']);
        Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
        Route::put('/service-categories/{category}', [ServiceCategoryController::class, 'update']);
        Route::delete('/service-categories/{category}', [ServiceCategoryController::class, 'destroy']);

        // Services routes
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{service}', [ServiceController::class, 'show']);
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

        // CRUD groupes + options
        Route::get('/option-groups', [OptionGroupController::class, 'index']);
        Route::post('/option-groups', [OptionGroupController::class, 'store']);
        Route::get('/option-groups/{group}', [OptionGroupController::class, 'show']);
        Route::put('/option-groups/{group}', [OptionGroupController::class, 'update']);
        Route::delete('/option-groups/{group}', [OptionGroupController::class, 'destroy']);

        Route::post('/option-groups/{group}/options', [OptionGroupController::class, 'storeOption']);
        Route::put('/option-groups/{group}/options/{option}', [OptionGroupController::class, 'updateOption']);
        Route::delete('/option-groups/{group}/options/{option}', [OptionGroupController::class, 'destroyOption']);

        // Attacher/détacher des groupes à une prestation
        Route::get('/services/{service}/option-groups', [ServiceOptionGroupAttachController::class, 'index']);
        Route::post('/services/{service}/option-groups/attach', [ServiceOptionGroupAttachController::class, 'attach']);
        Route::post('/services/{service}/option-groups/detach', [ServiceOptionGroupAttachController::class, 'detach']);
        Route::post('/services/{service}/option-groups/reorder', [ServiceOptionGroupAttachController::class, 'reorder']);

        // Upload images for options
        Route::post('/uploads', [UploadController::class, 'store']);

        // Image routes
        Route::prefix('images')->group(function () {
            Route::post('/', [ImageController::class, 'store']);
            Route::post('multiple', [ImageController::class, 'showMultiple']);
            Route::post('first-or-create', [ImageController::class, 'firstOrCreate']);
            Route::post('find-by-url', [ImageController::class, 'findImageByUrl']);
            Route::get('{image}', [ImageController::class, 'show']);
            Route::delete('{image}', [ImageController::class, 'destroy']);
        });
    });
});
