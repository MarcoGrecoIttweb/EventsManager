<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MercatinoAnnuncioStorage
{
    public const PREFIX = 'mercatino_annunci';

    /** Giorni di visibilità in vetrina dall’ancoraggio (pubblicazione o ultimo rinnovo). */
    public const VISIBILITY_DAYS = 30;

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

    /**
     * Rimuove dalla vetrina la cartella dell'annuncio (dati.json e foto) su entrambi i dischi noti.
     */
    public static function deletePublishedAnnuncio(string $folder): void
    {
        $relative = self::folderBase($folder);
        foreach (['upload_immagini', 'public'] as $diskName) {
            $disk = Storage::disk($diskName);
            if ($disk->exists($relative)) {
                $disk->deleteDirectory($relative);
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

    /**
     * Data/ora da cui contano i 30 giorni: `visibilita_da` se presente, altrimenti `inviato_il` (annunci legacy).
     */
    public static function visibilityAnchor(?array $dati): ?Carbon
    {
        if (! is_array($dati)) {
            return null;
        }
        $iso = trim((string) ($dati['visibilita_da'] ?? ''));
        if ($iso === '') {
            $iso = trim((string) ($dati['inviato_il'] ?? ''));
        }
        if ($iso === '') {
            return null;
        }
        try {
            return Carbon::parse($iso)->timezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    public static function expiresAt(?array $dati): ?Carbon
    {
        $anchor = self::visibilityAnchor($dati);
        if ($anchor === null) {
            return null;
        }

        return $anchor->copy()->addDays(self::VISIBILITY_DAYS);
    }

    public static function isExpired(?array $dati): bool
    {
        $exp = self::expiresAt($dati);
        if ($exp === null) {
            return false;
        }

        return $exp->isPast();
    }

    /**
     * Rimuove dalla vetrina gli annunci oltre la durata massima. Restituisce il numero di annunci eliminati.
     */
    public static function purgeExpired(): int
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

        $removed = 0;
        foreach (array_keys($folderNames) as $folder) {
            $decoded = self::readJsonDecoded($folder);
            if (! is_array($decoded)) {
                continue;
            }
            if (self::isExpired($decoded)) {
                self::deletePublishedAnnuncio($folder);
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Salva `dati.json` su ogni disco dove il file esiste già; se non esiste da nessuna parte, scrive su upload_immagini.
     */
    public static function saveAnnuncioJsonAllDisks(string $folder, array $decoded): void
    {
        $jsonPath = self::jsonPath($folder);
        $payload = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $written = false;
        foreach (['upload_immagini', 'public'] as $diskName) {
            if (Storage::disk($diskName)->exists($jsonPath)) {
                Storage::disk($diskName)->put($jsonPath, $payload);
                $written = true;
            }
        }
        if (! $written) {
            Storage::disk('upload_immagini')->put($jsonPath, $payload);
        }
    }
}
