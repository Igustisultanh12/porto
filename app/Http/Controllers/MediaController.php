<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Content;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function show(Request $request, Media $media): StreamedResponse
    {
        $allowed = $request->user()?->is_admin || ($media->content_id ? $media->content?->isPublished() : true);
        abort_unless($allowed && Storage::disk('local')->exists($media->path), 404);
        $headers = ['Content-Type' => $media->mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];
        if ($media->mime === 'application/pdf') {
            return Storage::disk('local')->download($media->path, $media->name, $headers);
        }

        return Storage::disk('local')->response($media->path, $media->name, $headers);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['content_id' => 'nullable|integer|exists:contents,id', 'slot' => ['required', Rule::in(['cover', 'gallery', 'pdf', 'avatar', 'logo', 'favicon', 'cv'])],
            'file' => ['required', 'file', 'max:10240', Rule::when(in_array($request->input('slot'), ['pdf', 'cv']), ['mimes:pdf'], ['mimes:jpg,jpeg,png,webp'])]]);
        $contentId = $data['content_id'] ?? null;
        if ($contentId) {
            abort_unless(in_array($data['slot'], ['cover', 'gallery', 'pdf']), 422);
            Content::findOrFail($contentId);
        } else {
            abort_unless(in_array($data['slot'], ['avatar', 'logo', 'favicon', 'cv']), 422);
        }
        $file = $request->file('file');
        $mime = $file->getMimeType();
        $name = $file->getClientOriginalName();
        if ($mime !== 'application/pdf') {
            if (! function_exists('imagecreatefromstring')) {
                throw ValidationException::withMessages(['file' => 'Extension PHP GD wajib aktif untuk optimasi gambar.']);
            }
            $size = getimagesize($file->getRealPath());
            if (! $size || $size[0] * $size[1] > 24000000) {
                throw ValidationException::withMessages(['file' => 'Gambar maksimal 24 megapiksel.']);
            }
            $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
            if (! $image) {
                throw ValidationException::withMessages(['file' => 'Gambar tidak dapat dibaca.']);
            }
            $width = min(1920, $size[0]);
            $scaled = imagescale($image, $width);
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
            ob_start();
            imagewebp($scaled, null, 82);
            $bytes = ob_get_clean();
            imagedestroy($image);
            imagedestroy($scaled);
            $path = 'media/'.Str::uuid().'.webp';
            Storage::disk('local')->put($path, $bytes);
            $mime = 'image/webp';
            $name = pathinfo($name, PATHINFO_FILENAME).'.webp';
        } else {
            $path = $file->store('media', 'local');
        }
        try {
            DB::transaction(function () use ($contentId, $data, $path, $name, $mime) {
                if ($data['slot'] !== 'gallery') {
                    // Replaced rows are detached; old files remain private for backup recovery.
                    Media::where('content_id', $contentId)->where('slot', $data['slot'])->delete();
                }
                $media = Media::create(['content_id' => $contentId, 'slot' => $data['slot'], 'path' => $path, 'name' => $name, 'mime' => $mime, 'size' => Storage::disk('local')->size($path)]);
                Activity::record('upload', 'media:'.$media->id);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return back()->with('success', 'Berkas diunggah.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        // A unique file belongs to one media row; retain private bytes for backups.
        $media->delete();
        Activity::record('detach','media:'.$media->id);

        return back()->with('success','Berkas dilepas dari situs.');
    }
}
