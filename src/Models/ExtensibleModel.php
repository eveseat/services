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

namespace Seat\Services\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use Seat\Services\Exceptions\InjectedRelationConflictException;
use Seat\Services\Services\InjectedRelationRegistry;

abstract class ExtensibleModel extends Model
{
    /**
     * Returns an attribute or relation of the model, considering injected relations.
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    public function __get($key)
    {
        // fetch injected relations
        $extension_registry = app()->make(InjectedRelationRegistry::class);
        $extension_class = $extension_registry->getExtensionClassFor($this::class, $key);

        // check if we have an injected relation
        if($extension_class){
            // we have an injected relation
            // since what we are doing is not intended, we have to roughly reimplement laravel's code from here on

            //check if relation data is cached
            // the relation cache continues to work even for 'fake' relations, but we have to manually call it
            if($this->relationLoaded($key)){
                return $this->getRelationValue($key);
            }

            // it is NOT cached, we have to load it and put it into the cache
            // get relation from extension
            $extension_class_instance = new $extension_class;
            $relation = $extension_class_instance->$key($this);

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
     * Redirects calls to injected relations or behaves like a normal class.
     *
     * @param  string  $method
     * @param  $parameters
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    public function __call($method, $parameters)
    {
        // fetch injected relations
        $extension_registry = app()->make(InjectedRelationRegistry::class);
        $extension_class = $extension_registry->getExtensionClassFor($this::class, $method);

        // check if we have an injected relation
        if($extension_class) {
            // return the injected relation
            $extension_class_instance = new $extension_class;

            return $extension_class_instance->$method($this);
        }

        // use the default behaviour if no relation is injected
        return parent::__call($method, $parameters);
    }

    /**
     * Injects relations into this model.
     *
     * @param  string  $extension_class  the class that provides the injected relations
     * @return void
     *
     * @throws BindingResolutionException
     * @throws InjectedRelationConflictException A conflict arises when trying to inject two relations with the same name into a target.
     */
    public static function injectRelationsFrom(string $extension_class): void {
        $registry = app()->make(InjectedRelationRegistry::class);
        $registry->injectRelations(static::class, $extension_class);
    }
}
