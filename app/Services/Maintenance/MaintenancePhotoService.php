<?php

namespace App\Services\Maintenance;

use App\Models\Maintenance;
use App\Models\MaintenancePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MaintenancePhotoService
{
    public function store(Maintenance $maintenance, UploadedFile $file, string $type): MaintenancePhoto
    {
        $path = $file->store("maintenance/{$maintenance->getKey()}", 'public');

        try {
            return DB::transaction(fn (): MaintenancePhoto => $maintenance->photos()->create([
                'type' => $type,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => (string) $file->getMimeType(),
                'size' => $file->getSize(),
            ]));
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    public function delete(MaintenancePhoto $photo): void
    {
        $path = $photo->path;
        $photo->delete();
        Storage::disk('public')->delete($path);
    }
}
