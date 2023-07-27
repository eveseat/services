<?php

namespace Seat\Tests\Services\InjectedRelations\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Seat\Services\Models\ExtensibleModel;
use Seat\Tests\Services\database\factories\ModelBFactory;


class ModelB extends ExtensibleModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = "model_b";

    /**
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory(): Factory
    {
        return ModelBFactory::new();
    }

    public function modelA(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelA::class);
    }
}