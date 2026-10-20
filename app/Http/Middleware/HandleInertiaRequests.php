<?php

namespace App\Http\Middleware;

use App\Models\Media;
use App\Models\ProfileEntry;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [...parent::share($request),
            'origin' => rtrim(config('app.url'), '/'),
            'site' => fn () => Setting::values(),
            'siteMedia' => fn () => Media::whereNull('content_id')->get(),
            'socials' => fn () => ProfileEntry::where('kind', 'social')->orderBy('sort_order')->get(),
            'auth' => fn () => ['user' => $request->user()?->only('id', 'name', 'email', 'is_admin')],
            'flash' => fn () => ['success' => $request->session()->get('success')],
        ];
    }
}
