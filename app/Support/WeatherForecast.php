<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherForecast
{
    // Milano: sede delle iniziative Excursio.
    private const LATITUDE = 45.4642;
    private const LONGITUDE = 9.1900;

    private const WEATHER_ICONS = [
        0 => ['icon' => 'fa-sun', 'label' => 'Sereno'],
        1 => ['icon' => 'fa-cloud-sun', 'label' => 'Poco nuvoloso'],
        2 => ['icon' => 'fa-cloud-sun', 'label' => 'Parzialmente nuvoloso'],
        3 => ['icon' => 'fa-cloud', 'label' => 'Nuvoloso'],
        45 => ['icon' => 'fa-smog', 'label' => 'Nebbia'],
        48 => ['icon' => 'fa-smog', 'label' => 'Nebbia'],
        51 => ['icon' => 'fa-cloud-rain', 'label' => 'Pioviggine'],
        53 => ['icon' => 'fa-cloud-rain', 'label' => 'Pioviggine'],
        55 => ['icon' => 'fa-cloud-rain', 'label' => 'Pioviggine'],
        61 => ['icon' => 'fa-cloud-showers-heavy', 'label' => 'Pioggia debole'],
        63 => ['icon' => 'fa-cloud-showers-heavy', 'label' => 'Pioggia'],
        65 => ['icon' => 'fa-cloud-showers-heavy', 'label' => 'Pioggia forte'],
        71 => ['icon' => 'fa-snowflake', 'label' => 'Neve debole'],
        73 => ['icon' => 'fa-snowflake', 'label' => 'Neve'],
        75 => ['icon' => 'fa-snowflake', 'label' => 'Neve forte'],
        80 => ['icon' => 'fa-cloud-showers-heavy', 'label' => 'Rovesci'],
        81 => ['icon' => 'fa-cloud-showers-heavy', 'label' => 'Rovesci'],
        82 => ['icon' => 'fa-cloud-showers-heavy', 'label' => 'Rovesci forti'],
        95 => ['icon' => 'fa-bolt', 'label' => 'Temporale'],
        96 => ['icon' => 'fa-bolt', 'label' => 'Temporale'],
        99 => ['icon' => 'fa-bolt', 'label' => 'Temporale forte'],
    ];

    /**
     * Previsioni per il prossimo sabato e domenica (il weekend in corso se e' gia'
     * sabato/domenica). Restituisce null se il servizio non e' raggiungibile.
     *
     * @return array<int, array{label: string, icon: string, description: string, min: int, max: int}>|null
     */
    public static function nextWeekend(): ?array
    {
        return Cache::remember('weather.next_weekend', now()->addHours(3), function () {
            try {
                $response = Http::timeout(3)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => self::LATITUDE,
                    'longitude' => self::LONGITUDE,
                    'daily' => 'weathercode,temperature_2m_max,temperature_2m_min',
                    'timezone' => 'Europe/Rome',
                    'forecast_days' => 10,
                ]);

                if (!$response->successful()) {
                    return null;
                }

                $daily = $response->json('daily');
                if (!is_array($daily) || empty($daily['time'])) {
                    return null;
                }

                $result = [];
                foreach ($daily['time'] as $index => $date) {
                    $carbonDate = \Carbon\Carbon::parse($date);
                    if (!in_array($carbonDate->dayOfWeekIso, [6, 7], true)) {
                        continue;
                    }

                    $code = (int) ($daily['weathercode'][$index] ?? 0);
                    $weather = self::WEATHER_ICONS[$code] ?? ['icon' => 'fa-cloud', 'label' => 'Variabile'];

                    $result[] = [
                        'label' => $carbonDate->locale('it')->translatedFormat('D j/n'),
                        'icon' => $weather['icon'],
                        'description' => $weather['label'],
                        'min' => (int) round($daily['temperature_2m_min'][$index] ?? 0),
                        'max' => (int) round($daily['temperature_2m_max'][$index] ?? 0),
                    ];

                    if (count($result) === 2) {
                        break;
                    }
                }

                return $result !== [] ? $result : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}
