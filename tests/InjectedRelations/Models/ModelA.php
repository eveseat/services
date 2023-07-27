<?php

namespace Seat\Tests\Services\InjectedRelations\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Seat\Services\Models\ExtensibleModel;
use Seat\Tests\Services\database\factories\ModelAFactory;


class ModelA extends ExtensibleModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = "model_a";

    /**
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory(): Factory
    {
        return ModelAFactory::new();
    }

    public function modelB(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ModelB::class);
    }
}