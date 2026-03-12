<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class ImageService
{
    /**
     * Upload image to storage
     */
    public function uploadImage(UploadedFile $image, string $folder = 'events'): array
    {
        try {
            // Crea il nome file univoco
            $extension = $image->getClientOriginalExtension();
            $filename = uniqid() . '_' . time() . '.' . $extension;

            // Salva l'immagine nel disco 'public' (storage/app/public)
            $path = Storage::disk('public')->putFileAs(
                $folder,
                $image,
                $filename
            );

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $path, // Questo sarà 'events/2/nomefile.jpg'
                'full_path' => Storage::disk('public')->path($path), // Percorso completo
                'url' => Storage::disk('public')->url($path), // URL pubblico
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize(),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload multiple images for event
     */
    public function uploadEventImages(array $images, int $eventId): array
    {
        $uploadedImages = [];
        $folder = "events/{$eventId}";

        foreach ($images as $image) {
            $result = $this->uploadImage($image, $folder);
            if ($result['success']) {
                $uploadedImages[] = $result;
            }
        }

        return $uploadedImages;
    }

    /**
     * Upload cover image to public/upload_immagini/ (condiviso con il sito legacy).
     * Salva solo il nome file in evento.immagine, come fa il sito legacy.
     */
    public function uploadCoverImage(UploadedFile $image, int $eventId): array
    {
        try {
            $extension = $image->getClientOriginalExtension();
            $filename = $eventId . '_' . time() . '.' . $extension;
            $destDir = public_path('upload_immagini');

            $image->move($destDir, $filename);

            return [
                'success'  => true,
                'filename' => $filename,
                'url'      => asset("upload_immagini/{$filename}"),
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete cover image from public/upload_immagini/
     */
    public function deleteCoverImage(string $filename): bool
    {
        $path = public_path("upload_immagini/{$filename}");
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Delete image from storage
     */
    public function deleteImage(string $path): bool
    {
        try {
            // Usa il disco 'public'
            return Storage::disk('public')->delete($path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete event folder and all images
     */
    public function deleteEventFolder(int $eventId): bool
    {
        try {
            // Usa il disco 'public'
            return Storage::disk('public')->deleteDirectory("events/{$eventId}");
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get public URL for image
     */
    public function getUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
