<?php

namespace Seat\Tests\Services\InjectedRelations\Extensions;

use Seat\Tests\Services\InjectedRelations\Models\ModelB;

class ModelAExtension
{
    public function modelBInjected($model): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $model->hasOne(ModelB::class);
    }
}