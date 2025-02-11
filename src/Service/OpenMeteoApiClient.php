<?php

namespace App\Service;

class OpenMeteoApiClient
{
    public function __construct(
        private ApiClient $apiClient,
        private GeocodeApiClient $geocodeApiClient
    ) {
    }

    public function getMeteoData(string $postal_code): array
    {
        // Transform the postal code into latitude and longitude
        $params = $this->geocodeApiClient->getGeocodePosition($postal_code);

        // Build the url
        $url = "https://api.open-meteo.com/v1/forecast?";

        $constants = [
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,rain,showers,snowfall,weather_code,cloud_cover,pressure_msl,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
            'forecast_days' => 1
        ];

        $url .= http_build_query($params) . '&' . http_build_query($constants);

        // Get the response
        return $this->apiClient->fetchData($url);
    }
}
