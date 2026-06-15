<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\WebAdminController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WebBookingController;
use App\Http\Controllers\WebMaintenanceController;
use App\Http\Controllers\WebFavoriteController;
use App\Http\Controllers\WebNotificationController;
use App\Http\Controllers\WebPaymentController;
use App\Http\Controllers\WebProfileController;
use App\Http\Controllers\WebChatController;
use App\Http\Controllers\WebComplaintController;
use App\Http\Controllers\WebPropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $avgRating = \App\Models\Review::avg('stars');
    $stats = [
        'properties'         => \App\Models\Property::count(),
        'activeUsers'        => \App\Models\User::count(),
        'completedBookings'  => \App\Models\Booking::where('status', 'completed')->count(),
        'satisfaction'       => $avgRating ? round(($avgRating / 5) * 100) : 98,
    ];
    return view('welcome', compact('stats'));
})->name('home');

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);

Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [WebAuthController::class, 'register']);

Route::get('/location-picker', function () {
    return view('location-picker');
})->name('location.picker');

Route::get('/verify-phone', function () {
    return view('auth.verify-phone');
})->name('verification.notice');

Route::get('/forgot-password', [WebAuthController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [WebAuthController::class, 'sendResetOtp'])->name('password.forgot.send');
Route::get('/reset-password', [WebAuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.reset.submit');

Route::get('/terms', function () {
    $content = config('settings.terms', '');
    return view('pages.terms', compact('content'));
})->name('terms');

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Plutu payment callback (no auth — Plutu redirects here)
Route::get('/plutu/callback/{booking}', [App\Http\Controllers\PlutuPaymentController::class, 'callback'])->name('plutu.callback');

Route::controller(WebPropertyController::class)->prefix('properties')->name('properties.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create')->middleware('auth');
    Route::post('/', 'store')->name('store')->middleware('auth');
    Route::get('/{property}', 'show')->name('show');
    Route::get('/{property}/edit', 'edit')->name('edit')->middleware('auth');
    Route::put('/{property}', 'update')->name('update')->middleware('auth');
    Route::delete('/{property}', 'destroy')->name('destroy')->middleware('auth');
});

// Test notifications (admin only)
Route::middleware(['auth', 'role:admin'])->get('/test-notification', function () {
    $userId = (int) request('user_id');
    if ($userId < 1) return 'Please add ?user_id=ID (e.g. ?user_id=3) to the URL';
    $notification = \App\Models\Notification::create([
        'user_id' => $userId,
        'title' => __('Test notification'),
        'content' => __('This is a test notification from Maskan system'),
        'type' => 'general',
    ]);
    broadcast(new \App\Events\NotificationCreated($notification));
    return 'Notification sent to user ' . $userId;
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(WebBookingController::class)->prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{booking}', 'show')->name('show');
        Route::get('/{booking}/confirm', 'confirm')->name('confirm');
        Route::get('/{booking}/checkin', 'checkin')->name('checkin');
        Route::get('/{booking}/complete', 'complete')->name('complete');
        Route::match(['GET', 'POST'], '/{booking}/cancel', 'cancel')->name('cancel');
    });

    // Plutu payment
    Route::prefix('plutu')->name('plutu.')->controller(\App\Http\Controllers\PlutuPaymentController::class)->group(function () {
        Route::get('/pay/{booking}', 'pay')->name('pay');
    });

    Route::controller(WebMaintenanceController::class)->prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{maintenance_request}', 'show')->name('show');
        Route::post('/{maintenance_request}/assign', 'assign')->name('assign');
        Route::post('/{maintenance_request}/reject', 'reject')->name('reject');
        Route::post('/{maintenance_request}/status', 'status')->name('status');
        Route::post('/{maintenance_request}/rate', 'rate')->name('rate');
    });

    Route::controller(WebNotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{notification}/read', 'markAsRead')->name('read');
        Route::get('/read-all', 'markAllAsRead')->name('readAll');
        Route::get('/{notification}/delete', 'destroy')->name('destroy');
    });

    Route::controller(WebProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
        Route::put('/password', 'password')->name('password');
        Route::post('/deactivate', 'deactivate')->name('deactivate');
        Route::post('/delete', 'destroyAccount')->name('destroy');
    });

    Route::controller(WebFavoriteController::class)->prefix('favorites')->name('favorites.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/toggle', 'toggle')->name('toggle');
        Route::get('/{favorite}/delete', 'destroy')->name('destroy');
    });

    Route::controller(WebChatController::class)->prefix('messages')->name('messages.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}', 'show')->name('show');
        Route::post('/{message}/edit', 'editMessage')->name('edit');
        Route::post('/{message}/delete', 'deleteMessage')->name('delete');
        Route::post('/conversation/{user}/delete', 'deleteConversation')->name('deleteConversation');
    });

    Route::controller(WebPaymentController::class)->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', 'index')->name('index');
    });

    // Complaints
    Route::controller(WebComplaintController::class)->prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{complaint}', 'show')->name('show');
        Route::post('/{complaint}/respond', 'respond')->name('respond');
        Route::put('/{complaint}/status', 'status')->name('status');
    });

    // ========================
    // Admin Routes
    // ========================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:admin,owner');

        Route::get('/users', [WebAdminController::class, 'users'])->name('users');
        Route::get('/users/create', [WebAdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [WebAdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}', [WebAdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [WebAdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [WebAdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/ban', [WebAdminController::class, 'banUser'])->name('users.ban');
        Route::get('/users/{user}/unban', [WebAdminController::class, 'unbanUser'])->name('users.unban');
        Route::post('/users/{user}/delete', [WebAdminController::class, 'destroyUser'])->name('users.destroy');

        Route::get('/properties', [WebAdminController::class, 'properties'])->name('properties');
        Route::get('/properties/pending', [WebAdminController::class, 'pendingProperties'])->name('properties.pending');
        Route::get('/properties/{property}/review', [WebAdminController::class, 'reviewProperty'])->name('properties.review');
        Route::post('/properties/{property}/request-approval', [WebAdminController::class, 'requestApproval'])->name('properties.request-approval');
        Route::post('/properties/{property}/approve', [WebAdminController::class, 'approveProperty'])->name('properties.approve');
        Route::post('/properties/{property}/reject', [WebAdminController::class, 'rejectProperty'])->name('properties.reject');
        Route::post('/properties/{property}/deactivate', [WebAdminController::class, 'deactivateProperty'])->name('properties.deactivate');

        Route::get('/bookings', [WebAdminController::class, 'bookings'])->name('bookings');
        Route::get('/maintenance', [WebAdminController::class, 'maintenanceRequests'])->name('maintenance');
        Route::get('/reports', [WebAdminController::class, 'reports'])->name('reports');

        Route::get('/complaints', [WebComplaintController::class, 'index'])->name('complaints');
        Route::get('/complaints/{complaint}', [WebComplaintController::class, 'show'])->name('complaints.show');
        Route::post('/complaints/{complaint}/reply', [WebComplaintController::class, 'respond'])->name('complaints.reply');

        Route::get('/notifications/broadcast', [WebAdminController::class, 'broadcastForm'])->name('notifications.broadcast');
        Route::post('/notifications/broadcast', [WebAdminController::class, 'sendBroadcast'])->name('notifications.broadcast.send');

        Route::get('/cities', [WebAdminController::class, 'cities'])->name('cities');
        Route::post('/cities', [WebAdminController::class, 'storeCity'])->name('cities.store');
        Route::delete('/cities/{city}', [WebAdminController::class, 'destroyCity'])->name('cities.destroy');

        Route::get('/archive', [WebAdminController::class, 'archive'])->name('archive');
        Route::post('/archive/run', [WebAdminController::class, 'runArchive'])->name('archive.run');
        Route::post('/archive/{booking}/restore', [WebAdminController::class, 'restoreArchive'])->name('archive.restore');

        Route::get('/settings', [WebAdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [WebAdminController::class, 'updateSettings'])->name('settings.update');
    });

    // ========================
    // Owner Routes
    // ========================
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');

        Route::get('/properties', [OwnerController::class, 'properties'])->name('properties');
        Route::get('/properties/{property}/availability', [OwnerController::class, 'availability'])->name('properties.availability');
        Route::post('/properties/{property}/availability', [OwnerController::class, 'storeAvailability'])->name('properties.availability.store');
        Route::delete('/properties/{property}/availability', [OwnerController::class, 'removeAvailability'])->name('properties.availability.remove');
        Route::post('/properties/{property}/toggle-status', [OwnerController::class, 'togglePropertyStatus'])->name('properties.toggle-status');

        Route::get('/bookings', [OwnerController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{booking}', [OwnerController::class, 'showBooking'])->name('bookings.show');

        Route::get('/maintenance', [OwnerController::class, 'maintenance'])->name('maintenance');
        Route::get('/maintenance/{request}', [OwnerController::class, 'showMaintenance'])->name('maintenance.show');

        Route::get('/timeline', [OwnerController::class, 'timeline'])->name('timeline');
        Route::get('/reports', [OwnerController::class, 'reports'])->name('reports');
        Route::get('/invoices', [OwnerController::class, 'invoices'])->name('invoices');
        Route::post('/invoices', [OwnerController::class, 'createInvoice'])->name('invoices.create');
    });
});
