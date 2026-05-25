<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class EventGreetingSettings
{
    private static ?bool $databaseReady = null;

    public static function isDatabaseReady(): bool
    {
        if (self::$databaseReady !== null) {
            return self::$databaseReady;
        }

        try {
            self::$databaseReady = Schema::hasTable('evento')
                && Schema::hasColumn('evento', 'greeting_box_enabled');
        } catch (\Throwable $e) {
            self::$databaseReady = false;
        }

        return self::$databaseReady;
    }
    public static function defaultMessageHtml(): string
    {
        return '<p>Ciao {nickname} {nome}, questo evento è fatto per te</p>';
    }

    public static function hasGreetingBox(Event $event): bool
    {
        if (! self::isDatabaseReady()) {
            return false;
        }

        return (bool) ($event->greeting_box_enabled ?? false);
    }

    public static function durationSecondsForEvent(Event $event): int
    {
        $value = (int) ($event->greeting_box_duration ?? 5);

        return max(1, min(120, $value ?: 5));
    }

    public static function messageHtmlForEvent(Event $event): string
    {
        $html = trim((string) ($event->greeting_box_message ?? ''));
        if ($html === '') {
            return self::defaultMessageHtml();
        }

        return $html;
    }

    public static function boxMaxWidthForEvent(Event $event): int
    {
        $value = (int) ($event->greeting_box_max_width ?? 420);

        return max(280, min(900, $value ?: 420));
    }

    public static function boxBorderColorForEvent(Event $event): string
    {
        return self::sanitizeHexColor(
            (string) ($event->greeting_box_border_color ?? '#198754'),
            '#198754'
        );
    }

    public static function boxBackgroundColorForEvent(Event $event): string
    {
        return self::sanitizeHexColor(
            (string) ($event->greeting_box_bg_color ?? '#ffffff'),
            '#ffffff'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function frontendConfigForEvent(Event $event, ?User $user): ?array
    {
        if (! self::hasGreetingBox($event) || ! $user) {
            return null;
        }

        return [
            'durationMs' => self::durationSecondsForEvent($event) * 1000,
            'messageHtml' => self::renderMessageForEvent($event, $user),
            'box' => [
                'maxWidth' => self::boxMaxWidthForEvent($event),
                'borderColor' => self::boxBorderColorForEvent($event),
                'backgroundColor' => self::boxBackgroundColorForEvent($event),
            ],
        ];
    }

    public static function renderMessageForEvent(Event $event, ?User $user): string
    {
        $html = self::messageHtmlForEvent($event);
        $nickname = trim((string) ($user?->nickname ?? ''));
        $nome = trim((string) ($user?->nome ?? ''));
        $cognome = trim((string) ($user?->cognome ?? ''));
        $fullName = trim($nome . ' ' . $cognome);

        $html = str_replace(
            ['{nickname}', '{nome}', '{cognome}', '{nome_completo}'],
            [e($nickname), e($nome), e($cognome), e($fullName)],
            $html
        );

        return SafeRichText::sanitize($html, false);
    }

    /**
     * @return array<string, mixed>
     */
    public static function payloadFromRequest(\Illuminate\Http\Request $request): array
    {
        if (! self::isDatabaseReady()) {
            return [];
        }

        $message = SafeRichText::sanitize((string) $request->input('greeting_box_message', ''), true);
        if ($message === '') {
            $message = self::defaultMessageHtml();
        }

        return [
            'greeting_box_enabled' => $request->boolean('greeting_box_enabled') ? 1 : 0,
            'greeting_box_duration' => max(1, min(120, (int) $request->input('greeting_box_duration', 5))),
            'greeting_box_message' => $message,
            'greeting_box_max_width' => max(280, min(900, (int) $request->input('greeting_box_max_width', 420))),
            'greeting_box_border_color' => self::sanitizeHexColor(
                (string) $request->input('greeting_box_border_color', '#198754'),
                '#198754'
            ),
            'greeting_box_bg_color' => self::sanitizeHexColor(
                (string) $request->input('greeting_box_bg_color', '#ffffff'),
                '#ffffff'
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        if (! self::isDatabaseReady()) {
            return [];
        }

        return [
            'greeting_box_enabled' => 'sometimes|boolean',
            'greeting_box_duration' => 'nullable|integer|min:1|max:120',
            'greeting_box_message' => 'nullable|string|max:20000',
            'greeting_box_max_width' => 'nullable|integer|min:280|max:900',
            'greeting_box_border_color' => 'nullable|string|max:20',
            'greeting_box_bg_color' => 'nullable|string|max:20',
        ];
    }

    public static function migrationRequiredMessage(): string
    {
        return 'Il box benvenuto richiede l\'aggiornamento database. Esegui sul server: php artisan migrate --force';
    }

    /**
     * @return array<string, string>|null
     */
    public static function migrationGuardError(\Illuminate\Http\Request $request): ?array
    {
        if (self::isDatabaseReady()) {
            return null;
        }

        $wantsGreeting = $request->boolean('greeting_box_enabled');

        if (! $wantsGreeting) {
            return null;
        }

        return ['greeting_box' => self::migrationRequiredMessage()];
    }

    public static function sanitizeHexColor(string $value, string $default): string
    {
        $value = trim($value);
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value)) {
            return $value;
        }

        return $default;
    }
}
