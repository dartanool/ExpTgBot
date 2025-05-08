<?php

namespace App\Http\Services\Client;

use Illuminate\Support\Facades\Http;

class ExpeditorClient
{
    private string $apiToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiToken = env('API_TOKEN');
        $this->baseUrl = env('API_BASE_URL');
    }

    public function send(string $method, array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '. $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.$method, $data);

        return $response->json();
    }
}
