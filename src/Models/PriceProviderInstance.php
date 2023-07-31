<?php

namespace Seat\Services\Models;

use Illuminate\Database\Eloquent\Model;

class PriceProviderInstance extends Model
{
    public $fillable = [
        'name', 'backend', 'configuration'
    ];

    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'configuration' => 'array',
    ];
}