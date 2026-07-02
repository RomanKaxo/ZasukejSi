<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    public function getUrl(string $conversionName = ''): string
    {
        if ($conversionName !== '' && ! $this->hasGeneratedConversion($conversionName)) {
            $conversionName = '';
        }

        return route('media.show', [
            'media' => $this,
            'filename' => $this->file_name,
            'conversion' => $conversionName !== '' ? $conversionName : null,
        ]);
    }
}