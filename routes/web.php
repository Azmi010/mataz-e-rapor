<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'admin' => redirect('/admin'),
            'teacher' => redirect('/teacher'),
            'student' => redirect('/student'),
            default => redirect('/auth/login'),
        };
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/teacher/grading/{student}/rapor-pdf', [ReportController::class, 'generateReport'])
    ->name('rapor.pdf');
