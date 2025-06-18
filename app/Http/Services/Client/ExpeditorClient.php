<?php

namespace App\Http\Services\Client;

use App\Models\Telegraph\TelegraphUsers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpeditorClient
{
    private string $baseUrl;
    private string $userId;
    public function __construct(string $userId)
    {
        $this->userId = $userId;
        $this->baseUrl = env('API_BASE_URL');
    }

    public function auth(string $method, string $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '. $data,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.$method);

        // Логирование полного ответа
        Log::debug('API Response', [
            'status' => $data,
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return null;
        }
    }
    public function send(string $method, array $data)
    {
        $response = Http::withHeaders([
            'Pragma' => "{$this->getToken()}",
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.$method, $data);


        // Логирование полного ответа
        Log::debug('API Response', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return null;
        }
    }

    public function getToken()
    {
        $token = TelegraphUsers::query()->where('user_id', $this->userId)->first()->token;
        if (isset($token)) {
            return $token;
        } else {
            return null;
        }
    }
}
