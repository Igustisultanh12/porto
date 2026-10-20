<?php

use App\Jobs\NotifyContact;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::call(function () {
    if (config('portfolio.notification_email')) {
        ContactMessage::whereNull('notified_at')->where('created_at', '<', now()->subMinutes(10))->orderBy('id')->chunkById(100, function ($messages) {
            foreach ($messages as $message) {
                NotifyContact::dispatch($message->id);
            }
        });
    }
})->name('retry-contact-notifications')->everyTenMinutes()->withoutOverlapping();
