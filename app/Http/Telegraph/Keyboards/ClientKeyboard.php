<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Keyboard\Keyboard;

class ClientKeyboard
{
    public static function handle(array $clients, string $addressId, string $tripId)
    {
        $keyboard = Keyboard::make();

        foreach($clients as $client) {

            $buttonText = sprintf(
                "📍 %s | %s",
                $client->id,
                $client->clientName);


            $keyboard->button($buttonText)
                ->action('selectClient')->param('clientId', $client->id)->param('addressId', $addressId);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Назад')->action('selectAddress')
                ->param('addressId', $addressId)
                ->param('tripId', $tripId);

        return $keyboard;
    }
}
