<?php

namespace App\Models\Telegraph;

use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property int $user_id
 * @property string $state
 * @property string $data
 */
class TelegramUserState extends Model
{
    protected $fillable = ['user_id', 'state', 'data'];
}
