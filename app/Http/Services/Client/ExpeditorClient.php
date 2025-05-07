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

    public function send(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '. $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'GetSession', $data);

        return $response->json();
    }
}
