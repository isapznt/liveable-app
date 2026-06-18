<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;


Route::get('/', function () {
    return json_encode('web');
});
// rotas auth
Route::get('/register', [UserController::class, 'registerForm']);
Route::post('/register', [UserController::class, 'register']);
Route::get('/login', [UserController::class, 'loginForm']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);
Route::get('/admin/users', [UserController::class, 'listUsers'])->middleware('can:admin');

// rotas de imoveis
Route::resource('/properties', PropertyController::class)->only('index', 'show', 'like');
Route::resource('/properties', PropertyController::class)->only('edit', 'destroy')->middleware('can:admin');


