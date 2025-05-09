<?php

use App\Http\Controllers\FollowerController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('home-page');


Route::get('/home', [PostController::class, 'index'])->name('home-page');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('post/create-post', [PostController::class, 'create'])->name('create-post');
    Route::post('post/create-post', [PostController::class, 'store'])->name('save-post');
    Route::get('/@{username}/{post:slug}', [PostController::class, 'show'])->name('single-post');
    Route::get('/post/{post}/edit', [PostController::class, 'edit'])->name('edit-post');
    Route::patch('/post/{post}', [PostController::class, 'update'])->name('update-post');
    Route::delete('/post/{post}', [PostController::class, 'destroy'])->name('delete-post');
    Route::post('/follow/{user}', [FollowerController::class, 'followUnfollow'])->name('follow');
    Route::get('/@{user:username}', [PublicProfileController::class, 'show'])->name('public-profile');
    Route::post('/like/{post}', [LikeController::class, 'like'])->name('like');
    Route::delete('/unlike/{post}', [LikeController::class, 'unlike'])->name('unlike');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
