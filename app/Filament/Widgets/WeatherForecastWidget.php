<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherForecastWidget extends Widget
{
    protected static string $view = 'filament.widgets.weather-forecast-widget';
    protected static ?int $sort = -9;
    protected int | string | array $columnSpan = 'full';

    // Weather is now displayed in the header bar via HeaderWeatherWidget
    public static function canView(): bool
    {
        return false;
    }

    public function mount(): void
    {
    }

    protected function getViewData(): array
    {
        $weather = Cache::remember('weather_forecast', now()->addMinutes(30), function () {
            $apiKey = config('services.openweather.key');
            $city = config('services.openweather.city', 'Manila, PH');

            if (empty($apiKey)) {
                return null;
            }

            try {
                $response = Http::timeout(5)->get("https://api.openweathermap.org/data/2.5/weather", [
                    'q' => $city,
                    'appid' => $apiKey,
                    'units' => 'metric'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::error('Weather API Error: ' . $e->getMessage());
            }

            return null;
        });

        return [
            'weather' => $weather,
            'city' => config('services.openweather.city', 'Manila, PH')
        ];
    }
}
