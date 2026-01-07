<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('LandingPage');
})->name('home');

Route::get('/convert', function () {
    return Inertia::render('Mp3ToMvPublic');
})->name('convert.public');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('mp3-to-mv', function () {
    return Inertia::render('Mp3ToMv');
})->middleware(['auth', 'verified'])->name('mp3-to-mv');

require __DIR__ . '/settings.php';
