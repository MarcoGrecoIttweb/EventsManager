<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MercatinoAnnuncioStorage
{
    public const PREFIX = 'mercatino_annunci';

    public static function folderBase(string $folder): string
    {
        return self::PREFIX . '/' . $folder;
    }

    public static function jsonPath(string $folder): string
    {
        return self::folderBase($folder) . '/dati.json';
    }

    public static function annuncioExists(string $folder): bool
    {
        $path = self::jsonPath($folder);

        return Storage::disk('upload_immagini')->exists($path)
            || Storage::disk('public')->exists($path);
    }

    public static function readJsonDecoded(string $folder): ?array
    {
        $path = self::jsonPath($folder);
        if (Storage::disk('upload_immagini')->exists($path)) {
            $decoded = json_decode(Storage::disk('upload_immagini')->get($path), true);

            return is_array($decoded) ? $decoded : null;
        }
        if (Storage::disk('public')->exists($path)) {
            $decoded = json_decode(Storage::disk('public')->get($path), true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    /**
     * URL pubblica per una foto slot (1–3), o null. Preferisce upload_immagini, poi legacy storage/public.
     */
    public static function photoPublicUrl(string $folder, int $slot): ?string
    {
        $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        foreach ($exts as $ext) {
            $rel = self::folderBase($folder) . '/foto_' . $slot . '.' . $ext;
            if (Storage::disk('upload_immagini')->exists($rel)) {
                return asset('upload_immagini/' . $rel);
            }
        }
        foreach ($exts as $ext) {
            $rel = self::folderBase($folder) . '/foto_' . $slot . '.' . $ext;
            if (Storage::disk('public')->exists($rel)) {
                return asset('storage/' . $rel);
            }
        }

        return null;
    }

    public static function deletePhotoIfExists(string $folder, int $slot): void
    {
        $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        foreach ($exts as $ext) {
            $rel = self::folderBase($folder) . '/foto_' . $slot . '.' . $ext;
            foreach (['upload_immagini', 'public'] as $diskName) {
                if (Storage::disk($diskName)->exists($rel)) {
                    Storage::disk($diskName)->delete($rel);
                }
            }
        }
    }

    public static function listPublished(): Collection
    {
        $folderNames = [];
        foreach (['upload_immagini', 'public'] as $diskName) {
            $disk = Storage::disk($diskName);
            if (! $disk->exists(self::PREFIX)) {
                continue;
            }
            foreach ($disk->directories(self::PREFIX) as $subdir) {
                $folderNames[basename($subdir)] = true;
            }
        }

        $annunci = collect();
        foreach (array_keys($folderNames) as $folder) {
            $decoded = self::readJsonDecoded($folder);
            if (! is_array($decoded)) {
                continue;
            }
            $annunci->push([
                'cartella' => $folder,
                'dati' => $decoded,
            ]);
        }

        return $annunci->sortByDesc(function ($row) {
            return $row['dati']['inviato_il'] ?? '';
        })->values();
    }
}
