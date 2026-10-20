<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Jobs\NotifyContact;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        $message = ContactMessage::firstOrCreate(['submission_id' => $request->validated('submission_id')], $request->safe()->except('website'));
        if ($message->wasRecentlyCreated && config('portfolio.notification_email')) {
            try {
                NotifyContact::dispatch($message->id);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('success', 'Pesanmu sudah tersimpan. Terima kasih telah menghubungi.');
    }
}
