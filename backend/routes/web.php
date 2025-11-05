<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home/Landing page
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [GoogleAuthController::class, 'redirectToGoogle'])->name('login');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Logout
Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Teacher Portal
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/materials', [DashboardController::class, 'materials'])->name('materials');
        Route::get('/materials/create', [DashboardController::class, 'createMaterial'])->name('materials.create');
        Route::get('/materials/{materialId}', [DashboardController::class, 'showMaterial'])->name('materials.show');
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    });

    // Admin Portal
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::get('/materials', [AdminDashboardController::class, 'materials'])->name('materials');
        Route::get('/jobs', [AdminDashboardController::class, 'jobs'])->name('jobs');
        Route::get('/audit-logs', [AdminDashboardController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/templates', [AdminDashboardController::class, 'templates'])->name('templates');
        Route::get('/system', [AdminDashboardController::class, 'system'])->name('system');
        Route::get('/configuration', [AdminDashboardController::class, 'configuration'])->name('configuration');
    });
});

// API Documentation
Route::get('/api/docs', function () {
    return view('api.docs');
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});