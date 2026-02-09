<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\SermonController;
use Illuminate\Support\Facades\Route;

// Open Routes
Route::get('/', [PostController::class, 'index'])->name('home-page');
Route::get('/home', [PostController::class, 'indexHome']);
Route::get('/category/{category:title}', [PostController::class, 'category'])->name('byCategory');

// Auth, Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/@{username}/{post:slug}', [PostController::class, 'show'])->name('single-post');
    Route::get('/report-post/{post:id}', [ReportingController::class, 'index'])->name('report-form');
    Route::post('/report/{post:id}', [ReportingController::class, 'report'])->name('report');
    Route::get('/search', [PostController::class, 'search'])->name('search');
    Route::post('/follow/{user}', [FollowerController::class, 'followUnfollow'])->name('follow')->middleware(['throttle:followLimit']);
    Route::get('/@{user:username}', [PublicProfileController::class, 'show'])->name('public-profile');
    Route::post('/like/{post:id}', [LikeController::class, 'like'])->middleware(['throttle:likeLimit']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/follower-list', [ProfileController::class, 'showFollowers'])->name('follower-list');
    Route::get('/search-authors', [PostController::class, 'searchAuthor'])->name('search-authors');
    Route::get('/search-posts', [PostController::class, 'searchPost'])->name('search-posts');
    Route::get('/positions', [ListingController::class, 'showPositions'])->name('positions');
    Route::get('/position/{position:id}', [ListingController::class, 'showPosition'])->name('show-position');
    Route::get('/create-listing', [ListingController::class, 'index'])->name('create-listing');
    Route::post('/create-listing', [ListingController::class, 'store'])->name('save-listing');
    Route::get('/position/{position:id}/edit', [ListingController::class, 'edit'])->name('edit-listing');
    Route::patch('/position/{position:id}/save', [ListingController::class, 'update'])->name('update-listing');
});

// Auth, Verified, Subscribed Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Posts
    Route::get('/post/create-post', [PostController::class, 'create'])->name('create-post');
    Route::post('/post/create-post', [PostController::class, 'store'])->name('save-post');
    Route::get('/post/{post:slug}/edit', [PostController::class, 'edit'])->name('edit-post');
    Route::patch('/post/{post:slug}/publish', [PostController::class, 'publish'])->name('publish-post');
    Route::patch('/post/{post:slug}', [PostController::class, 'update'])->name('update-post');
    Route::delete('/post/{post:id}/delete', [PostController::class, 'destroy'])->name('delete-post');
    Route::get('/learn-markdown', [PostController::class, 'markdownSandbox'])->name('markdown-sandbox');
});

// Auth, Verified, Admin Routes
Route::middleware(['auth', 'verified', 'can:is-admin'])->group(function () {
    // Ads
    Route::get('/create-ad', [AdController::class, 'index'])->name('create-ad');
    Route::post('/create-ad', [AdController::class, 'store'])->name('save-ad');
    Route::get('/edit-ad/{ad:id}', [AdController::class, 'edit'])->name('edit-ad');
    Route::patch('/update-ad/{ad:id}', [AdController::class, 'update'])->name('update-ad');
    Route::delete('/delete-ad/{ad:id}/', [AdController::class, 'delete'])->name('delete-ad');
    // Admin
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/users/{user:id}/make-admin', [AdminController::class, 'makeAdmin'])->name('make-admin');
    Route::post('/admin/users/{user:id}/revoke-admin', [AdminController::class, 'revokeAdmin'])->name('revoke-admin');
    Route::get('/admin/reported-posts', [AdminController::class, 'reported'])->name('admin.reported');
    Route::delete('/admin/users/{user:id}/delete', [AdminController::class, 'deleteUser']);
    Route::delete('/delete-report/{report:id}', [ReportingController::class, 'delete'])->name('delete-report');
    // Sermons
    Route::get('/sermons', [SermonController::class, 'index'])->name('sermons.index');
    Route::get('/sermons/create', [SermonController::class, 'create'])->name('create-sermon');
    Route::post('/sermons', [SermonController::class, 'store'])->name('sermons.store');
});

Route::get('/file-upload', function () {
    return view('fileUpload');
});

Route::post('/file-upload', function () {
    request()->file('file')->store(
        'sermons/my-file',
        'Wasabi'
    );

    // return 'File uploaded successfully';
    return redirect()->back();
})->name('file-upload');

require __DIR__.'/auth.php';
