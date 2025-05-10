<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Services\TelegraphService;
use DefStudio\Telegraph\Telegraph;
use \App\Http\Telegraph\API\GetTaskListAPI;

class GetTaskList
{
    private TelegraphService $telegraphService;
    private GetTaskListAPI $getTaskListAPI;

    public function __construct(int $chatId)
    {
        $this->telegraphService = new TelegraphService($chatId);
        $this->getTaskListAPI = new GetTaskListAPI();
    }
    public function handle(int $userId)
    {
        $text = 'Вот ваш список';
        $this->telegraphService->sendMessage($text);

        $response = $this->getTaskListAPI->handle($userId);

//        if (isset($response)) {
//            $this->telegraphService->sendMessage('lf');
//        }
//        $this->telegraphService->sendMessage('no');
        $this->telegraphService->sendApiResponse($response);
    }
}
