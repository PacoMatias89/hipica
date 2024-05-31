<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HorseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MailController;
use App\Http\Controllers\Api\BokkingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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

