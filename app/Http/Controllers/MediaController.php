<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function show(Request $request, Media $media, string $filename)
    {
        $conversion = (string) $request->query('conversion', '');

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

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}