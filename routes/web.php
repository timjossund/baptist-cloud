<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\PublicProfileController;

Route::get('/', [PostController::class, 'index'])->name('home-page');
Route::get('/home', [PostController::class, 'indexHome']);
Route::get('/category/{category}', [PostController::class, 'category'])->name('byCategory');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('post/create-post', [PostController::class, 'create'])->name('create-post');
    Route::post('post/create-post', [PostController::class, 'store'])->name('save-post');
    Route::get('/@{username}/{post:slug}', [PostController::class, 'show'])->name('single-post');
    Route::get('/post/{post}/edit', [PostController::class, 'edit'])->name('edit-post');
    Route::patch('/post/{post}', [PostController::class, 'update'])->name('update-post');
    Route::delete('/post/{post}/delete', [PostController::class, 'destroy'])->name('delete-post');
    Route::post('/follow/{user}', [FollowerController::class, 'followUnfollow'])->name('follow');
    Route::get('/@{user:username}', [PublicProfileController::class, 'show'])->name('public-profile');
    Route::post('/like/{post}', [LikeController::class, 'like'])->name('like');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::delete('/admin/users/{user:id}/delete', [AdminController::class, 'deleteUser']);

});

require __DIR__ . '/auth.php';
