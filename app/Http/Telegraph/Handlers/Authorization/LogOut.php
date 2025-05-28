<?php

namespace App\Http\Telegraph\Handlers\Authorization;

use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUsers;
use DefStudio\Telegraph\Models\TelegraphChat;

class LogOut
{
    public TelegraphChat $chat;
    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
    }
    public function handle()
    {
        TelegraphUserLocation::destroy($this->chat->id);
        TelegraphUsers::destroy($this->chat->id);
        $keyboard = '';
        $this->chat->message('Вы вышли ')->removeReplyKeyboard()->send();
    }
}
