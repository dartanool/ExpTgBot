<?php

namespace App\Http\Telegraph\Handlers\Authorization;
use App\Http\Telegraph\API\GetSession;
use App\Models\Telegraph\TelegraphUsers;
use App\Models\Telegraph\TelegramUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Support\Facades\Http;

class CompleteAuthHandler
{
    private string $apiToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiToken = env('API_TOKEN');
        $this->baseUrl = env('API_BASE_URL');
    }

    public function handle(int $userId, string $login, string $password): void
    {
        $getSession = new GetSession();
        $token = $getSession->handle($login, $password);

        if ($token) {
            Telegraph::message("Вы успешно авторизовались")->send();
            Telegraph::message("$token")->send();

            $this->location();

            TelegraphUsers::updateOrCreate(
                ['user_id' => $userId],
                ['token' => $token]
            );

            TelegramUserState::where('user_id', $userId)->delete();
        }
    }

//    private function getSession(string $login, string $password): string
//    {
//        $response = Http::withHeaders([
//            'Authorization' => 'Basic '. $this->apiToken,
//            'Content-Type' => 'application/json',
//        ])->post($this->baseUrl.'GetSession', [
//            'Username' => $login,
//            'Password' => $password
//        ]);
//
//        return $response->json()['Pragma'] ?? '';
//    }

    public function location()
    {
        Telegraph::message("На какой станции вы находитесь")->send();
    }
}
