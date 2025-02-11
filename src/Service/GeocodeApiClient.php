<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GeocodeApiClient
{
    private ApiClient $apiClient;
    private CacheInterface $cache;

    public function __construct(ApiClient $apiClient, CacheInterface $cache)
    {
        $this->apiClient = $apiClient;
        $this->cache = $cache;
    }

    public function getGeocodePosition(string $postalCode): array
    {
        return $this->cache->get('geocode_' . $postalCode, function (ItemInterface $item) use ($postalCode) {
            // No cache expiration
            $item->expiresAfter(null);

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
                throw new HttpException(404, 'Catcode not found in France or invalid');
            }

            // Manage multiple responses (first is most relevant)
            if (is_array($response)) {
                $response = $response[0];
            }

            // Get the latitude and longitude
            $lat = $response['lat'];
            $lon = $response['lon'];

            return [
                'latitude' => $lat,
                'longitude' => $lon
            ];
        });
    }
}
