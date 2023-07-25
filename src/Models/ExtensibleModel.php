<?php

namespace Seat\Services\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use Seat\Services\Services\ModelExtensionRegistry;

abstract class ExtensibleModel extends Model
{
    public function __get($key)
    {
        // fetch model extensions
        $extension_registry = app()->make(ModelExtensionRegistry::class);
        $extensionClass = $extension_registry->getExtension($this::class, $key);

        // if we have an extension, handle it
        if($extensionClass){
            // since what we are doing is not intended, we have to roughly reimplement laravel's code from here on

            //check if relation is cached
            // the relation cache continues to work even for 'fake' relations, but we have to manually call it
            if($this->relationLoaded($key)){
                return $this->getRelationValue($key);
            }

            // it is NOT cached, we have to load it and put it into the cache
            // get relationship
            $extensionClassInstance = new $extensionClass;
            $relation = $extensionClassInstance->$key($this);

            // check if we actually got a relation returned
            if (! $relation instanceof Relation) {
                if (is_null($relation)) {
                    throw new LogicException(sprintf(
                        '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', static::class, $key
                    ));
                }

                throw new LogicException(sprintf(
                    '%s::%s must return a relationship instance.', static::class, $key
                ));
            }

            return tap($relation->getResults(), function ($results) use ($key) {
                $this->setRelation($key, $results);
            });
        }

        // we have no extension, use default behaviour
        return parent::__get($key);
    }

}