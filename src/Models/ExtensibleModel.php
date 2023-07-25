<?php

namespace Seat\Services\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use Seat\Services\Services\InjectedRelationRegistry;

abstract class ExtensibleModel extends Model
{
    /**
     * Returns an attribute or relation of the model, considering injected relations
     * @param $key
     * @return mixed
     * @throws BindingResolutionException
     */
    public function __get($key)
    {
        // fetch injected relations
        $extension_registry = app()->make(InjectedRelationRegistry::class);
        $extensionClass = $extension_registry->getRelation($this::class, $key);

        // check if we have an injected relation
        if($extensionClass){
            // since what we are doing is not intended, we have to roughly reimplement laravel's code from here on

            //check if relation data is cached
            // the relation cache continues to work even for 'fake' relations, but we have to manually call it
            if($this->relationLoaded($key)){
                return $this->getRelationValue($key);
            }

            // it is NOT cached, we have to load it and put it into the cache
            // get relation from extension
            $extensionClassInstance = new $extensionClass;
            $relation = $extensionClassInstance->$key($this);

            // the following code is taken from laravel's \Illuminate\Database\Eloquent\Concerns\HasAttributes::getRelationshipFromMethod
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

    /**
     * Redirects calls to injected relations, or uses the default laravel behaviour otherwise
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws BindingResolutionException
     */
    public function __call($method, $parameters)
    {
        // fetch injected relations
        $extension_registry = app()->make(InjectedRelationRegistry::class);
        $extensionClass = $extension_registry->getRelation($this::class, $method);

        // check if we have an injected relation
        if($extensionClass) {
            // return the injected relation
            $extensionClassInstance = new $extensionClass;
            return $extensionClassInstance->$method($this);
        }

        // use the default behaviour if no relation is injected
        return parent::__call($method, $parameters);
    }
}