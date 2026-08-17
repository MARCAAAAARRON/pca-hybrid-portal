<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HeaderWeatherWidget extends Component
{
    public function render()
    {
        $city = config('services.openweather.city', 'Bohol, PH');

        $weather = Cache::get('weather_forecast');

        if (! $weather) {
            $apiKey = config('services.openweather.key');

            if (! empty($apiKey)) {
                try {
                    $response = Http::timeout(3)->get("https://api.openweathermap.org/data/2.5/weather", [
                        'q' => $city,
                        'appid' => $apiKey,
                        'units' => 'metric'
                    ]);

                    if ($response->successful() && isset($response->json()['main'])) {
                        $weather = $response->json();
                        Cache::put('weather_forecast', $weather, now()->addMinutes(30));
                    }
                } catch (\Exception $e) {
                    Log::error('Weather API Error: ' . $e->getMessage());
                }
            }
        }

        // Fallback default data so the weather widget NEVER disappears
        if (! $weather || ! isset($weather['main'])) {
            $weather = [
                'weather' => [
                    [
                        'description' => 'Overcast Clouds',
                        'icon' => '04d',
                    ]
                ],
                'main' => [
                    'temp' => 27,
                    'humidity' => 86,
                    'pressure' => 1011,
                ],
                'wind' => [
                    'speed' => 5,
                ],
            ];
        }

        return view('livewire.header-weather-widget', [
            'weather' => $weather,
            'city' => $city,
        ]);
    }
}
