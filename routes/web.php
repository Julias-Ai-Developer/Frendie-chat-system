<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->middleware(['auth'])->name('home');

require __DIR__.'/settings.php';
