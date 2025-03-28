<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\TransaccionController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/transacciones/registrar', [TransaccionController::class, 'registrarTransaccion']);
Route::get('/transacciones', [TransaccionController::class, 'getAllTransacciones']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
