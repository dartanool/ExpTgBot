<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Keyboard;

class ClientKeyboard
{
    public static function show(array $clients, string $tripId, string $addressId)
    {
        $keyboard = Keyboard::make();

        foreach($clients as $client) {

            $buttonText = sprintf(
                "📍 %s | %s",
                $client->id,
                $client->clientName);

            $keyboard->button($buttonText)
                ->action('selectClient')
                ->param('clientId', $client->id)
                ->param('tripId', $tripId);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Отмена')->action('cancel_trips');

        return $keyboard;
    }
}
