<?php

namespace App\Service;

class GeocodeApiClient
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function getGeocodeData(string $postalCode): array
    {
        // Get the .env api key
        $key = $_ENV['GEOCODING_KEY'];

        // Build the url
        $url = "https://geocode.maps.co/search?";

        $params = [
            'postalcode' => $postalCode,
            'country' => 'FR',
            'api_key' => $key
        ];

        $url .= http_build_query($params);

        // Get the response
        $response = $this->apiClient->fetchData($url);

        // Manage an empty response
        if (empty($response)) {
            return ['error' => 'No data found'];
        }

        // Manage multiple responses (first is most relevant)
        if (is_array($response)) {
            $response = $response[0];
        }

        // Get the latitude and longitude
        $lat = $response['lat'];
        $lon = $response['lon'];

        return [
            'lat' => $lat,
            'lon' => $lon
        ];
    }
}
