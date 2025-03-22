<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

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

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $response->getContent(true);
        }

        return $response->toArray();
    }
}