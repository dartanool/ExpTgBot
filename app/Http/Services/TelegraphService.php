<?php

namespace App\Http\Services;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;

class TelegraphService
{
    private TelegraphChat $chat;

    public function __construct(int $chatId)
    {
        $bot = TelegraphBot::query()->first();
        $this->chat = TelegraphChat::firstOrCreate([
            'chat_id' => $chatId,
            'telegraph_bot_id' => $bot->id
        ]);
    }

    public function sendMessage(string $text, array $buttons = []): void
    {
        $message = Telegraph::chat($this->chat)->message($text);

//        if (!empty($buttons)) {
//            $keyboard = Keyboard::make();
//            foreach ($buttons as $button) {
//                $keyboard->buttons([
//                    Button::make($button['text'])->action($button['action'])
//                ]);
//            }
//            $message->keyboard($keyboard);
//        }

        $message->send();
    }

    public function sendApiResponse(array $apiResponse): void
    {
        $formattedMessage = $this->formatApiResponse($apiResponse);
        Telegraph::chat($this->chat)
            ->message($formattedMessage)
            ->send();
    }

    private function formatApiResponse(array $response): string
    {
        $message = "📊 <b>{$response['REP_NAME']}</b>\n";
        $message .= "📝 {$response['REP_DESCRIPTION']}\n\n";

        if (!empty($response['REP_PARAMS'])) {
            $message .= "🔹 <b>Параметры:</b>\n";
            foreach ($response['REP_PARAMS'] as $param) {
                $message .= "— {$param['KGNAME']}\n";
            }
            $message .= "\n";
        }

        if (!empty($response['REP_FIELDS'])) {
            $fields = $this->parseFields($response['REP_FIELDS']);
            $message .= "🔹 <b>Доступные поля:</b>\n";
            foreach ($fields as $field) {
                $message .= "— {$field['name']} ({$field['description']})\n";
            }
        }

        return $message;
    }
}
