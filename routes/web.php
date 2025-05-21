<?php

use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\PublicProfileController;

Route::get('/', [PostController::class, 'index'])->name('home-page');
Route::get('/home', [PostController::class, 'indexHome']);
Route::get('/category/{category:title}', [PostController::class, 'category'])->name('byCategory');

Route::middleware(['auth', 'verified'
//    'subscribed'
])->group(function () {
    Route::get('post/create-post', [PostController::class, 'create'])->name('create-post');
    Route::post('post/create-post', [PostController::class, 'store'])->name('save-post');
    Route::get('/post/{post:slug}/edit', [PostController::class, 'edit'])->name('edit-post');
    Route::patch('/post/{post:slug}/publish', [PostController::class, 'publish'])->name('publish-post');
    Route::patch('/post/{post:slug}', [PostController::class, 'update'])->name('update-post');
    Route::delete('/post/{post}/delete', [PostController::class, 'destroy'])->name('delete-post');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/@{username}/{post:slug}', [PostController::class, 'show'])->name('single-post');
    Route::post('/follow/{user}', [FollowerController::class, 'followUnfollow'])->name('follow');
    Route::get('/@{user:username}', [PublicProfileController::class, 'show'])->name('public-profile');
    Route::post('/like/{post}', [LikeController::class, 'like'])->name('like');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::delete('/admin/users/{user:id}/delete', [AdminController::class, 'deleteUser']);
    Route::get('/search-authors', [PostController::class, 'searchAuthor'])->name('search-authors');
    Route::get('/search-posts', [PostController::class, 'searchPost'])->name('search-posts');
    Route::get('/positions', [ListingController::class, 'showPositions'])->name('positions');
    Route::get('/position/{position}', [ListingController::class, 'showPosition'])->name('show-position');
    Route::get('/create-listing', [ListingController::class, 'index'])->name('create-listing');
    Route::post('/create-listing', [ListingController::class, 'store'])->name('save-listing');
});

require __DIR__ . '/auth.php';
