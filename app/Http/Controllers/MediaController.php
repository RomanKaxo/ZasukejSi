<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Profile;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function show(Request $request, Media $media, string $filename)
    {
        $profileMedia = in_array($media->model_type, [Profile::class, (new Profile)->getMorphClass()], true);

        if ($profileMedia) {
            $profile = $media->model;
            $user = $request->user();

            abort_unless($profile instanceof Profile && (
                ($profile->is_public && $profile->status === 'approved')
                || ($user && (int) $profile->user_id === (int) $user->id)
                || ($user && $user->hasAnyRole(['admin', 'super_admin']))
            ), 404);
        }

        $conversion = $request->query('conversion', '');
        abort_unless(is_string($conversion), 404);

        if ($conversion !== '' && ! $media->hasGeneratedConversion($conversion)) {
            $conversion = '';
        }

        // The filename segment is part of the canonical URL, so reject requests
        // that do not match the stored media — previously any arbitrary name
        // served the same file, giving every asset unlimited duplicate URLs.
        // Spatie's URL generator always uses the original file name (even for
        // conversions, which are selected via the ?conversion= query), so that
        // is what we compare against.
        abort_unless($filename === $media->file_name, 404);

        $path = $conversion !== '' ? $media->getPath($conversion) : $media->getPath();

        abort_unless(is_string($path) && is_file($path), 404);

        $response = response()->file($path, [
            // A public profile can be unpublished later. Neither an owner's
            // preview nor a previously public photo may live in a shared cache.
            'Cache-Control' => $profileMedia ? 'private, no-store' : 'public, max-age=86400',
        ]);

        // BinaryFileResponse marks files public during construction, even if a
        // private Cache-Control header was supplied. Set it after construction.
        if ($profileMedia) {
            $response->setPrivate();
        }

        return $response;
    }
}
