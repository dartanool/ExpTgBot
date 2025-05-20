<?php

namespace App\Http\Telegraph\Keyboards;

use App\Http\Telegraph\Handlers\CompleteTask;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Keyboard;

class ClientKeyboard
{
    public static function handle(array $clients, string $addressId)
    {
        $keyboard = Keyboard::make();

        foreach($clients as $client) {

            $buttonText = sprintf(
                "📍 %s | %s",
                $client->id,
                $client->clientName);


            $keyboard->button($buttonText)
                ->action('selectClient')->param('clientName', $client->clientName)->param('addressId', $addressId);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Отмена')->action('cancel_trips');

        return $keyboard;
    }
}
