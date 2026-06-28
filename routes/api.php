<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiComplaintController;
use App\Http\Controllers\ApiMessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PlutuPaymentController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// Public routes (with rate limiting)
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'registerWeb'])->middleware('throttle:10,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

Route::get('/settings/public', function () {
    return response()->json([
        'terms' => config('settings.terms', ''),
    ]);
});

Route::get('/specialties', function () {
    $names = [
        'electricity' => 'كهرباء',
        'plumbing' => 'سباكة',
        'air_conditioning' => 'تكييف',
        'painting' => 'دهان',
        'carpentry' => 'نجارة',
        'other' => 'غير ذلك',
    ];
    return \App\Models\Specialty::all(['id', 'name'])->map(function ($s) use ($names) {
        $s->display_name = $names[$s->name] ?? $s->name;
        return $s;
    });
});

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{property}', [PropertyController::class, 'show']);

// Authenticated routes
Route::middleware(['auth', 'check.banned'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/profile/photo', [AuthController::class, 'uploadPhoto']);
    Route::delete('/auth/profile/photo', [AuthController::class, 'deletePhoto']);
    Route::post('/auth/deactivate', [AuthController::class, 'deactivate']);
    Route::post('/auth/delete', [AuthController::class, 'deleteAccount']);

    // Properties (Owner)
    Route::get('/owner/properties', [PropertyController::class, 'myProperties']);
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::put('/properties/{property}', [PropertyController::class, 'update']);
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);
    Route::put('/properties/{property}/status', [PropertyController::class, 'toggleStatus']);

    // Property availability
    Route::get('/properties/{property}/availability', [PropertyController::class, 'availability']);
    Route::get('/properties/{property}/blackout-dates', [PropertyController::class, 'availability']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::put('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    Route::put('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    Route::get('/owner/bookings', [BookingController::class, 'propertyBookings']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::put('/payments/{payment}/complete', [PaymentController::class, 'complete']);

    // Maintenance Requests
    Route::get('/maintenance-requests', [MaintenanceController::class, 'index']);
    Route::post('/maintenance-requests', [MaintenanceController::class, 'store']);
    Route::get('/maintenance-requests/{maintenance_request}', [MaintenanceController::class, 'show']);
    Route::put('/maintenance-requests/{maintenance_request}/assign', [MaintenanceController::class, 'assignTechnician']);
    Route::put('/maintenance-requests/{maintenance_request}/reject', [MaintenanceController::class, 'rejectRequest']);
    Route::put('/maintenance-requests/{maintenance_request}/status', [MaintenanceController::class, 'updateStatus']);
    Route::get('/technician/maintenance-requests', [MaintenanceController::class, 'technicianRequests']);
    Route::get('/technician/maintenance-requests/pending', [MaintenanceController::class, 'pendingRequests']);
    Route::put('/technician/maintenance-requests/{maintenance_request}/claim', [MaintenanceController::class, 'claimRequest']);
    Route::get('/maintenance-requests/{maintenance_request}/suggestions', [MaintenanceController::class, 'getAiSuggestions']);
    Route::post('/maintenance-requests/{maintenance_request}/ai-feedback', [MaintenanceController::class, 'aiFeedback']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::post('/favorites/check', [FavoriteController::class, 'check']);
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    Route::get('/properties/{property}/reviews', [ReviewController::class, 'propertyReviews']);

    // Plutu
    Route::post('/plutu/initiate', [PlutuPaymentController::class, 'apiInitiate']);
    Route::post('/plutu/check/{booking_id}', [PlutuPaymentController::class, 'apiCheck']);

    // Messages / Conversations
    Route::get('/conversations', [ApiMessageController::class, 'conversations']);
    Route::get('/conversations/{user}/messages', [ApiMessageController::class, 'messages']);
    Route::delete('/conversations/{user}', [ApiMessageController::class, 'deleteConversation']);
    Route::post('/messages', [ApiMessageController::class, 'send']);
    Route::put('/messages/{message}', [ApiMessageController::class, 'edit']);
    Route::delete('/messages/{message}', [ApiMessageController::class, 'destroy']);
    Route::post('/messages/{user}/read', [ApiMessageController::class, 'markAsRead']);

    // Complaints
    Route::get('/complaints', [ApiComplaintController::class, 'index']);
    Route::post('/complaints', [ApiComplaintController::class, 'store']);
    Route::get('/complaints/{complaint}', [ApiComplaintController::class, 'show']);

    // Admin routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::put('/users/{user}/ban', [AdminController::class, 'banUser']);
        Route::put('/users/{user}/unban', [AdminController::class, 'unbanUser']);
        Route::get('/properties', [AdminController::class, 'properties']);
        Route::put('/properties/{property}/approve', [AdminController::class, 'approveProperty']);
        Route::put('/properties/{property}/reject', [AdminController::class, 'rejectProperty']);
        Route::get('/bookings', [AdminController::class, 'bookings']);
        Route::get('/maintenance-requests', [AdminController::class, 'maintenanceRequests']);
        Route::get('/reports', [AdminController::class, 'reports']);
    });
});
