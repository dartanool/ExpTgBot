<?php

namespace App\Http\Services\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpeditorClient
{
    private string $apiToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiToken = env('API_TOKEN');
        $this->baseUrl = env('API_BASE_URL');
    }

    public function auth(string $method, array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '. $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.$method, $data);

        Log::debug('API Response', [
//            'method' => $method,
            'status' => $response->status(),
//            'headers' => $response->headers(),
            'body' => $response->json()
        ]);

        return $response->json();
    }

    public function send(string $method, array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '. $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.$method, $data);


        // Логирование полного ответа
        Log::debug('API Response', [
//            'method' => $method,
            'status' => $response->status(),
//            'headers' => $response->headers(),
            'body' => $response->json()
        ]);

        return $response->json();
    }

}
