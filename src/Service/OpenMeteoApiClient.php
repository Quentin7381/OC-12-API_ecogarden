<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Response;

use App\Service\Validator\Validator;

class OpenMeteoApiClient
{

    public function __construct(
        private ApiClient $apiClient,
        private GeocodeApiClient $geocodeApiClient,
        private Validator $validator,
        private CacheInterface $cache
    ) {
        $this->cache = $cache;
    }

    public function getMeteoData(string $postal_code): array
    {
        return $this->cache->get('meteo_' . $postal_code, function (ItemInterface $item) use ($postal_code) {
            $item->expiresAfter(3600); // Cache for 1 hour

            // Check postal code
            $this->validator->validate(null, [
                'additional_data' => [
                    'postal_code' => 'user::postal_code'
                ]
            ], ['postal_code' => $postal_code]);

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
            try {
                $response = $this->apiClient->fetchData($url);
            } catch (TransportException $e) {
                throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'External API Open Meteo did not respond in time.');
            }

            return $response;
        });
    }
}
