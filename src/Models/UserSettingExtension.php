<?php

namespace Seat\Services\Models;

use Seat\Web\Models\User;

class UserSettingExtension extends UserSetting
{
    public function user($model){
        return $model->belongsTo(User::class,"user_id","id");
    }
}