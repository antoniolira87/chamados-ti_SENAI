<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ChamadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/cadastro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/cadastro', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('chamados', ChamadoController::class);
    Route::patch('chamados/{chamado}/status', [ChamadoController::class, 'atualizarStatus'])
        ->name('chamados.status');

    Route::middleware('can:admin')->group(function () {
        Route::resource('categorias', CategoriaController::class);
        Route::resource('usuarios', UserController::class)->parameters(['usuarios' => 'usuario']);
    });
});
