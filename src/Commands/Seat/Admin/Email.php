<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Services\Commands\Seat\Admin;

use Illuminate\Console\Command;
use Seat\Services\Settings\Seat;

/**
 * Class Email.
 * @package Seat\Services\Commands\Seat\Admin
 */
class Email extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:admin:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the administrator email.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {

        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function handle()
    {

        $this->line('SeAT Admin Email Set Tool');

        $this->info('The current admin email is: ' . Seat::get('admin_contact'));
        $this->question('Please enter the new administrator email address:');

        $email = $this->ask('Email: ');
        while (! filter_var($email, FILTER_VALIDATE_EMAIL)) {

            // invalid email address
            $this->error($email . ' is not a valid email. Please try again:');
            $email = $this->ask('Email: ');
        }

        $this->info('Setting the administrator email to: ' . $email);
        Seat::set('admin_contact', $email);
    }
}
