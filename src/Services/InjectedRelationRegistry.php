<?php

namespace Seat\Services\Services;

use Seat\Services\Exceptions\InjectedRelationConflictException;

class InjectedRelationRegistry
{
    /**
     * Lookup table to search for injected relations.
     * Maps the result from getInjectionTargetKey to a class
     * @var array<string, string>
     */
    private array $relations = [];

    /**
     * Injects all relations from a class into a model
     * @param string $target_model the model to inject relations into
     * @param string $extension_class the class to take the relations from
     * @throws InjectedRelationConflictException A conflict arises when trying to inject two relations with the same name into a target.
     * @return void
     */
    public function injectRelations(string $target_model, string $extension_class): void
    {
        $methods = get_class_methods($extension_class);

        foreach ($methods as $relation_name){
            $this->injectSingleRelation($target_model, $extension_class, $relation_name);
        }
    }

    /**
     * Injects a single relation into a model
     * @param string $model the model to inject the relation into
     * @param string $extension_class the class holding the relation function
     * @param string $relation the name of the relation to be injected. The method providing the relation in $extension_class must have the same name, and the relation will be accessible under this name.
     * @return void
     * @throws InjectedRelationConflictException A conflict arises when trying to inject two relations with the same name into a target.
     */
    public function injectSingleRelation(string $model, string $extension_class, string $relation): void
    {
        $key = $this->getInjectionTargetKey($model, $relation);

        // check for conflicts, as there can't be two relations with the same name
        if(array_key_exists($key, $this->relations)) {
            $conflict = $this->relations[$key];
            throw new InjectedRelationConflictException(sprintf('Relation \'%s\' from \'%s\' is name-conflicting with \'%s\'', $relation, $model, $conflict ));
        }

        $this->relations[$key] = $extension_class;
    }

    /**
     * Searches for injected relations for a model
     * @param string $model the model to search for
     * @param string $relation the relation name to search for
     * @return string|null the class providing the injected relation, or null if there is no injected relation
     */
    public function getExtensionClassFor(string $model, string $relation): ?string
    {
        return $this->relations[$this->getInjectionTargetKey($model, $relation)] ?? null;
    }

    /**
     * Generates a key for $this->$relations
     * @param string $model the injection target class
     * @param string $relation_name the relation name
     * @return string a key to use with $this->$relations
     */
    private function getInjectionTargetKey(string $model, string $relation_name): string {
        return sprintf("%s.%s", $model, $relation_name);
    }
}