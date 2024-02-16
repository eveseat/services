<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

use Illuminate\Database\Migrations\Migration;
use Seat\Services\Models\UserSetting;

class ConvertChineseLanguageValue extends Migration
{
    protected static $prefix = 'profile';

    protected static $scope = 'user';

    /**
     * Run the migrations.
     *
     * @return void
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function up()
    {
        UserSetting::where([
            ['name', 'language'],
            ['value', '"cn"'],
        ])->get()->each(function (UserSetting $setting) {
            setting(['language', 'zh-CN', $setting->user_id]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function down()
    {
        UserSetting::where([
            ['name', 'language'],
            ['value', '"zh-CN"'],
        ])->get()->each(function (UserSetting $setting) {
            setting(['language', 'cn', $setting->user_id]);
        });
    }
}
