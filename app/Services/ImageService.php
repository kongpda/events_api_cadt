<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class ImageService
{
    public function upload(
        ?UploadedFile $file,
        string $path,
        ?string $oldImage = null
    ): ?string {
        if ($oldImage) {
            $this->delete($oldImage);
        }

        if ( ! $file) {
            return null;
        }

        return $file->store($path, 'public');
    }

    public function delete(?string $path): bool
    {
        if ( ! $path) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }

    public function url(?string $path): ?string
    {
        if ( ! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
