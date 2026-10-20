<?php

namespace App\Jobs;

use App\Models\ContactMessage;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifyContact implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 600;

    public function __construct(public int $messageId) {}

    public function uniqueId(): string
    {
        return (string) $this->messageId;
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $message = ContactMessage::find($this->messageId);
        $recipient = config('portfolio.notification_email');
        if (! $message || $message->notified_at || ! $recipient) {
            return;
        }
        Mail::raw('Pesan baru telah tersimpan di inbox admin. Buka '.route('admin.messages').' untuk membacanya.', function ($mail) use ($recipient) {
            $mail->to($recipient)->subject('Pesan kontak baru — Portofolio');
        });
        $message->update(['notified_at' => now()]);
    }
}
