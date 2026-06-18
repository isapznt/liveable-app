<?php

use App\Http\Controllers\PropertyLikeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyRentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyReviewController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Auth\SocialAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function () {
    return 'isso ta funcionando';
});

// Auth
Route::post('/register', [UserController::class, 'register']);
Route::post('/login',    [UserController::class, 'login']);
Route::get('/logout',    [UserController::class, 'logout'])->middleware('auth:sanctum');

// Públicas
Route::get('/properties',                      [PropertyController::class,       'index']);
Route::get('/properties/featured',             [PropertyController::class,       'featured']);
Route::get('/property/{property}',             [PropertyController::class,       'show']);
Route::get('/properties/{property}/reviews',   [PropertyReviewController::class, 'index']);
Route::get('/user/{user}',                     [UserController::class,           'show']);
Route::post('/webhooks/abacatepay', [PaymentController::class, 'webhook']);
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);
Route::get('/testar-email', function () {
    // Busca o primeiro usuário qualquer que já existe no seu banco local
    $user = User::first();

    if (!$user) {
        return 'Nenhum usuário encontrado no banco para testar. Crie pelo menos um no phpMyAdmin.';
    }

    // Cria dados fictícios para o teste
    $urlFake = 'http://localhost:5173/redefinir-senha?token=token_de_teste_123&email=' . urlencode($user->email);

    // Dispara a notificação diretamente usando a classe que você já criou
    $user->notify(new ResetPasswordNotification($urlFake, $user->name));

    return 'E-mail de teste disparado com sucesso para: ' . $user->email;
});
// Autenticadas
Route::middleware('auth:sanctum')->group(function () {

    // Properties
    Route::post('/property/store', [PropertyController::class, 'store']);
    Route::put('/property/update/{property}', [PropertyController::class, 'update']);
    Route::delete('/property/delete/{property}', [PropertyController::class, 'destroy']);
    Route::get('/my-properties', [PropertyController::class, 'myProperties']);
    Route::patch('/property/{property}/toggle-enabled', [PropertyController::class, 'toggleEnableProperty']);
    Route::get('/properties/{property}/my-rent', [PropertyController::class, 'myRent']);

    // Rents
    Route::post('/properties/{property}/rent', [PropertyRentController::class, 'store']);
    Route::get('/properties/{property}/rent', [PropertyRentController::class, 'index']);
    Route::get('/my-properties/pending-rents', [PropertyRentController::class, 'pendingRents']);
    Route::patch('/rents/{rent}/status', [PropertyRentController::class, 'updateStatus']);

    // Likes
    Route::post('/property/{property}/like', [PropertyLikeController::class, 'toggleLike']);

    // Reviews
    Route::post('/properties/{property}/reviews', [PropertyReviewController::class, 'store']);

    // Users
    Route::put('/user', [UserController::class, 'updateMe']);
    Route::post('/user/photo', [UserController::class, 'updatePhoto']);
    Route::post('/user/banner', [UserController::class, 'updateBanner']);

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/stats',                                [AdminController::class,    'stats']);
        Route::get('/users',                                [AdminController::class,    'listUsers']);
        Route::post('/create-admin',                        [AdminController::class,    'createAdmin']);
        Route::patch('/users/{user}/role',                  [AdminController::class,    'changeRole']);
        Route::patch('/properties/{property}/featured',     [PropertyController::class, 'toggleFeatured']); // ← aqui
    });

    // Likes
    Route::get('/favorites', [PropertyLikeController::class, 'myLikes']);

    // Payments
    Route::get('/payments/my', [PaymentController::class, 'myPayments']);
    Route::get('/payments/{payment}/qrcode', [PaymentController::class, 'getQrCode']);
    Route::post('/payments/{payment}/check', [PaymentController::class, 'checkStatus']);
    Route::post('/payments/{payment}/simulate', [PaymentController::class, 'simulate']);

    Route::get('/rents/active', [PropertyRentController::class, 'activeRents']);
});

