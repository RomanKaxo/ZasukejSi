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

        $path = $conversion !== '' ? $media->getPath($conversion) : $media->getPath();

        abort_unless(is_string($path) && is_file($path), 404);

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}