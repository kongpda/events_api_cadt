<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UploadImage
{
    public function execute(
        ?UploadedFile $file,
        string $path,
        ?string $oldImage = null
    ): ?string {
        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        if ( ! $file) {
            return null;
        }

        return $file->store($path, 'public');
    }
}
