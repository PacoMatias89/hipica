<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use App\Http\Controllers\Api\HorseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MailController;
use App\Http\Controllers\Api\BokkingController;
=======
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
<<<<<<< HEAD

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('horse', HorseController::class);
     //Rutas crud de reservas
    Route::resource('booking', BokkingController::class);

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('email', [MailController::class, 'sendEmail']);  
    Route::put('user/profile', [AuthController::class, 'updateProfile']);

  
});

Route::middleware('auth:sanctum')->get('/user/details', function (Request $request) {
    return $request->user();
});
=======
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
