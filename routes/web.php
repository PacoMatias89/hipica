<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
<<<<<<< HEAD
use App\Http\Controllers\Web\BookingController;
=======
use App\Http\Controllers\Web\BokkingController;
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\StripeController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
<<<<<<< HEAD
    Route::get('/dashboard', [BookingController::class, 'index'])->name('dashboard');
=======
    Route::get('/dashboard', [BokkingController::class, 'index'])->name('dashboard');
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80

    // Rutas CRUD de caballos
    Route::resource('horse', HorseController::class);

    // Rutas CRUD de reservas
<<<<<<< HEAD
    Route::resource('bookings', BookingController::class);

    // Ruta para descargar el PDF del detalle de la reserva
    Route::get('/bookings/{id}/pdf', [BookingController::class, 'showPdf'])->name('Booking.show.pdf');
});


=======
    Route::resource('bookings', BokkingController::class);

    // Ruta para descargar el PDF del detalle de la reserva
    Route::get('/bookings/{id}/pdf', [BokkingController::class, 'showPdf'])->name('bookings.show.pdf');
});

>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
// Email 
Route::get('/email', [EmailController::class, 'create']);
Route::post('/email', [EmailController::class, 'sendEmail'])->name('send.email');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth:sanctum')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard');
})->middleware(['auth:sanctum'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
<<<<<<< HEAD
})->middleware(['auth:sanctum'])->name('verification.send');
=======
})->middleware(['auth:sanctum'])->name('verification.send');
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
