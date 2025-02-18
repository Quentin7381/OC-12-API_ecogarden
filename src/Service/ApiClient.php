<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchData(string $url): array
    {
        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            $response->getContent(true);
        }

        return $response->toArray();
    }
}