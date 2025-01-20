<?php

use App\Events\AntrianUpdate;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/antrian', [HomeController::class, 'antrian'])->name('home.antrian');

Route::get('/update-antrian', function() {
    broadcast(new AntrianUpdate(json_encode([
        'current' => 90,
        'next' => 91
    ])));
    return response()->json(['status' => 'Success!']);
});