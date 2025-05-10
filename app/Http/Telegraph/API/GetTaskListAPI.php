<?php

namespace App\Http\Telegraph\API;

use App\Http\Services\TelegraphService;
use App\Models\Telegraph\TelegraphUsers;

class GetTaskListAPI extends BaseAPI
{
    public function handle(int $userId)
    {
        $token = TelegraphUsers::query()->where('user_id', $userId)->first()->token;
        $method = 'rt';

        $data = [
            'Pragma' => "$token",
            'init' => [
                'type' => 'data',
                'report' => 'te.kg.r'
            ],
            'params' => [
                'KgName' => '%'
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);
// Проверка что ответ не пустой
        if (empty($response)) {
            throw new \Exception("API вернул пустой ответ");
        }

        // Проверка наличия обязательных полей
        $requiredFields = ['RESULT', 'REP_NAME', 'REP_DESCRIPTION'];
        foreach ($requiredFields as $field) {
            if (!isset($response[$field])) {
                throw new \Exception("В ответе API отсутствует обязательное поле: {$field}");
            }
        }

        // Проверка статуса выполнения
        if ($response['RESULT'] != '1') {
            throw new \Exception("API запрос не выполнен. Код ошибки: {$response['RESULT']}");
        }

//        if (isset($response)){
//            (new TelegraphService($userId))->sendMessage('yeyeyey');
//            die;
//        }
//
//        (new TelegraphService($userId))->sendMessage('nonononon');

        return $response;
    }
}
