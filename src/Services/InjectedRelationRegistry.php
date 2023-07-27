<?php

namespace Seat\Services\Services;

use Seat\Services\Exceptions\InjectedRelationConflictException;

class InjectedRelationRegistry
{
    /**
     * @var array<string, string>
     */
    private array $relations = [];

    private function getInjectionTargetKey(string $model, string $extension_name): string {
        return sprintf("%s.%s", $model, $extension_name);
    }

    /**
     * @throws InjectedRelationConflictException
     */
    public function injectRelations(string $target_model, string $extension_class): void
    {
        $methods = get_class_methods($extension_class);

        foreach ($methods as $relation_name){
            $this->registerRelation($target_model, $extension_class, $relation_name);
        }
    }

    /**
     * @throws InjectedRelationConflictException
     */
    public function registerRelation(string $model, string $extension, string $relation): void
    {
        $key = $this->getInjectionTargetKey($model, $relation);

        // check for conflicts, as there can't be two relations with the same name
        if(array_key_exists($key, $this->relations)) {
            $conflict = $this->relations[$key];
            throw new InjectedRelationConflictException(sprintf('Relation \'%s\' from \'%s\' is name-conflicting with \'%s\'', $relation, $model, $conflict ));
        }

        $this->relations[$key] = $extension;
    }

    public function getExtensionClassFor(string $model, string $relation): ?string
    {
        return $this->relations[$this->getInjectionTargetKey($model, $relation)] ?? null;
    }
}