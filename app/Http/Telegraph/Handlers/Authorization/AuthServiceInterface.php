<?php

namespace App\Http\Telegraph\Handlers\Authorization;


use DefStudio\Telegraph\Handlers\EmptyWebhookHandler;

interface AuthServiceInterface
{

    public function handle(string $text = null): void;
}
