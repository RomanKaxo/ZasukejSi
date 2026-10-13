<?php

namespace App\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class MediaUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        $conversion = $this->conversion?->getName() ?? '';
        if ($conversion !== '' && ! $this->media->hasGeneratedConversion($conversion)) {
            $conversion = '';
        }

        // Resources that query Spatie's base Media class must use the same
        // authorized route as App\Models\Media, including conversion previews.
        return route('media.show', [
            'media' => $this->media->getKey(),
            'filename' => $this->media->file_name,
            'conversion' => $conversion !== '' ? $conversion : null,
        ]);
    }
}
