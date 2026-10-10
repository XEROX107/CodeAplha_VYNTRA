<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\NotificationController;

// Home
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Guest Routes: Register and Login
Route::middleware('guest')->group(function () {

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('posts.destroy');

    // Likes
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])
        ->name('posts.like');

    // Comments
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    Route::put('/comments/{comment}', [CommentController::class, 'update'])
        ->name('comments.update');

    // Follow and Unfollow Users
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])
        ->name('users.follow');

    // Search Users (must come before the dynamic profile route)
    Route::get('/users/search', [UserSearchController::class, 'index'])
        ->name('users.search');

    // User Profiles
    Route::get('/users/{user}', [ProfileController::class, 'show'])
        ->name('users.show');

    Route::get('/users/{user}/followers', [ProfileController::class, 'followers'])
        ->name('users.followers');

    Route::get('/users/{user}/following', [ProfileController::class, 'following'])
        ->name('users.following');

    // Edit Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.readAll');
});
