<?php

namespace App\Http\Services;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;

class TelegraphService
{
    private TelegraphChat $chat;

    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
//        $bot = TelegraphBot::query()->first();
//        $this->chat = TelegraphChat::query()->where('id', $chatId)->chat_id;
//        $this->chat = TelegraphChat::firstOrCreate([
//            'id' => $chatId,
//            'telegraph_bot_id' => $bot->id
//        ]);
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
}
