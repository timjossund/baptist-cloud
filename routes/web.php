<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [PostController::class, 'index'])->name('home-page');
    Route::get('post/create-post', [PostController::class, 'create'])->name('create-post');
    Route::post('post/create-post', [PostController::class, 'store'])->name('save-post');
    Route::get('/post/{post}', [PostController::class, 'show'])->name('single-post');
    Route::get('/post/{post}/edit', [PostController::class, 'edit'])->name('edit-post');
    Route::patch('/post/{post}', [PostController::class, 'update'])->name('update-post');
    Route::delete('/post/{post}', [PostController::class, 'destroy'])->name('delete-post');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
