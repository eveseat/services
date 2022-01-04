<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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
use Illuminate\Support\Facades\DB;

class ConvertSsoScopesToList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $sso_scopes = DB::table('global_settings')
            ->where('name', 'sso_scopes')
            ->first();

        if (is_null($sso_scopes))
            return;

        $new_sso_scopes = [
            [
                'id'      => 0,
                'name'    => 'default',
                'scopes'  => json_decode($sso_scopes->value),
                'default' => true,
            ],
        ];

        DB::table('global_settings')
            ->where('name', 'sso_scopes')
            ->update(['value' => json_encode($new_sso_scopes)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $sso_scopes = DB::table('global_settings')
            ->where('name', 'sso_scopes')
            ->first();

        if(! is_null($sso_scopes)) {
            $sso_scopes = json_decode($sso_scopes->value);
            $old_sso_scopes = [];
            foreach($sso_scopes as $scope) {
                if ($scope->default == true) {
                    $old_sso_scopes = $scope->scopes;
                }
            }

            DB::table('global_settings')
                ->where('name', 'sso_scopes')
                ->update(['value' => $old_sso_scopes]);
        }
    }
}
