<?php

namespace App\MediaLibrary;

use Filament\Facades\Filament;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class NMRPlatformPathGenerator extends DefaultPathGenerator
{
    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $currentTenant = Filament::getTenant();

        if ($currentTenant) {
            $mediaModel = $media->model;
            if ($mediaModel && $media->model_type == 'App\Models\Sample') {
                return $currentTenant->slug.'-'.$currentTenant->reference.DIRECTORY_SEPARATOR.$mediaModel->reference;
            }

            return $currentTenant->slug.'-'.$currentTenant->reference.DIRECTORY_SEPARATOR.$media->getKey();
        }

        return $media->getKey();
    }
}
