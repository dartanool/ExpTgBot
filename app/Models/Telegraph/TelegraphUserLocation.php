<?php

namespace App\Models\Telegraph;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $city_id
 * @property int $station_id
 */
class TelegraphUserLocation extends Model
{
    protected $fillable = [
        'user_id',
        'city_id',
        'station_id',
    ];
}
