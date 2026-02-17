<?php

use App\Http\Controllers\Api\v1\AddressController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\ImageController;
use App\Http\Controllers\Api\v1\Pro\AvailabilityController;
use App\Http\Controllers\Api\v1\Pro\OptionGroupController;
use App\Http\Controllers\Api\v1\Pro\ProductCategoryController;
use App\Http\Controllers\Api\v1\Pro\ProductController;
use App\Http\Controllers\Api\v1\Pro\ProfessionalWorkplaceController;
use App\Http\Controllers\Api\v1\Pro\ProfileController;
use App\Http\Controllers\Api\v1\Pro\ResourceController;
use App\Http\Controllers\Api\v1\Pro\ServiceCategoryController;
use App\Http\Controllers\Api\v1\Pro\ServiceController;
use App\Http\Controllers\Api\v1\Pro\ServiceOptionGroupAttachController;
use App\Http\Controllers\Api\v1\UploadController;
use App\Models\User;
use Faker\Factory;
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

        //Services
        Route::prefix("service-categories")->group(function () {
            Route::get('/', [ServiceCategoryController::class, 'index']);
            Route::get('/{category}', [ServiceCategoryController::class, 'show']);
            Route::post('/', [ServiceCategoryController::class, 'store']);
            Route::put('/{category}', [ServiceCategoryController::class, 'update']);
            Route::delete('/{category}', [ServiceCategoryController::class, 'destroy']);
        });
        Route::prefix("services")->group(function () {
            Route::get('/', [ServiceController::class, 'index']);
            Route::get('/{service}', [ServiceController::class, 'show']);
            Route::post('/', [ServiceController::class, 'store']);
            Route::put('/{service}', [ServiceController::class, 'update']);
            Route::delete('/{service}', [ServiceController::class, 'destroy']);

            // Attacher/détacher des groupes à une prestation
            Route::get('/{service}/option-groups', [ServiceOptionGroupAttachController::class, 'index']);
            Route::post('/{service}/option-groups/attach', [ServiceOptionGroupAttachController::class, 'attach']);
            Route::post('/{service}/option-groups/detach', [ServiceOptionGroupAttachController::class, 'detach']);
            Route::post('/{service}/option-groups/reorder', [ServiceOptionGroupAttachController::class, 'reorder']);
        });

        //Products
        Route::prefix("product-categories")->group(function () {
            Route::get('/', [ProductCategoryController::class, 'index']);
            Route::get('/{category}', [ProductCategoryController::class, 'show']);
            Route::post('/', [ProductCategoryController::class, 'store']);
            Route::put('/{category}', [ProductCategoryController::class, 'update']);
            Route::delete('/{category}', [ProductCategoryController::class, 'destroy']);
        });

        Route::prefix("products")->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
        });





        // CRUD groupes + options
        Route::prefix("option-groups")->group(function () {
            Route::get('/', [OptionGroupController::class, 'index']);
            Route::post('/', [OptionGroupController::class, 'store']);
            Route::get('/{group}', [OptionGroupController::class, 'show']);
            Route::put('/{group}', [OptionGroupController::class, 'update']);
            Route::delete('/{group}', [OptionGroupController::class, 'destroy']);

            Route::post('/{group}/options', [OptionGroupController::class, 'storeOption']);
            Route::put('/{group}/options/{option}', [OptionGroupController::class, 'updateOption']);
            Route::delete('/{group}/options/{option}', [OptionGroupController::class, 'destroyOption']);
        });


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
        Route::prefix('profile')->group(function () {
            Route::put('/', [ProfileController::class, 'update']);
            Route::put('/avatar', [ProfileController::class, 'updateAvatar']);
        });


        Route::prefix('resources')->group(function () {
            Route::get('/types', [ResourceController::class, 'resourceTypes']);
            Route::get('/', [ResourceController::class, 'index']);
            Route::post('/', [ResourceController::class, 'store']);
            Route::get('/{resource}', [ResourceController::class, 'show']);
            Route::put('/{resource}', [ResourceController::class, 'update']);
            Route::delete('/{resource}', [ResourceController::class, 'destroy']);

            // Update resource image
            Route::put('/{resource}/image', [ResourceController::class, 'updateImage']);
        });

        Route::prefix('pro/')->group(function () {
            Route::get('/availability', [AvailabilityController::class, 'show']);
            Route::put('/availability', [AvailabilityController::class, 'update']);

            Route::get("/workplaces", [ProfessionalWorkplaceController::class, 'index']);
            Route::post("/workplaces", [ProfessionalWorkplaceController::class, 'store']);
            Route::get("/workplaces/{workplace}", [ProfessionalWorkplaceController::class, 'show']);
            Route::put("/workplaces/{workplace}", [ProfessionalWorkplaceController::class, 'update']);
            Route::delete("/workplaces/{workplace}", [ProfessionalWorkplaceController::class, 'destroy']);
        });

        Route::prefix('addresses')->group(function () {
           Route::get('/', [AddressController::class, 'index']);
           Route::get('/{address}', [AddressController::class, 'show']);
           Route::post('/', [AddressController::class, 'store']);
           Route::put('/{address}', [AddressController::class, 'update']);
        });
    });
});
