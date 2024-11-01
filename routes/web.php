<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\addDataController;
use App\Http\Controllers\faceRecognitionController;

# Add New Data View
Route::get('/', function () {
    return view('addData');
});

# Presence View
Route::get('/presence', function () {
    return view('facePresence');
});

# Add Data Controller
Route::post('webcam-capture', [addDataController::class, 'store'])->name('webcam.capture');

# Presence Controller
Route::post('presence-capture', [faceRecognitionController::class, 'capture'])->name('presence.capture');
