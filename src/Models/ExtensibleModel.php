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

    public function __call($method, $parameters)
    {
        // fetch injected relations
        $extension_registry = app()->make(ModelExtensionRegistry::class);
        $extensionClass = $extension_registry->getExtension($this::class, $method);

        // check if we have an injected relation
        if($extensionClass) {
            // return the injected relation
            $extensionClassInstance = new $extensionClass;
            return $extensionClassInstance->$method($this);
        }

        // use the default behaviour if no relation is injected
        return parent::__call($method, $parameters);
    }

    public function __get($key)
    {
        // fetch injected relations
        $extension_registry = app()->make(ModelExtensionRegistry::class);
        $extensionClass = $extension_registry->getExtension($this::class, $key);

        // check if we have an injected relation
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

        // use the default behaviour if no relation is injected
        return parent::__get($key);
    }

}