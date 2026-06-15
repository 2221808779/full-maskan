<?php

use App\Models\Notification;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Notification::whereNull('read_at')
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
})->daily()->name('clean-old-notifications');

Schedule::call(function () {
    \App\Models\Booking::where('status', 'pending')
        ->where('created_at', '<', now()->subDays(7))
        ->update(['status' => 'cancelled']);
})->daily()->name('auto-cancel-expired-bookings');

Schedule::command('ai:predict')->weekly()->name('generate-ai-predictions');
