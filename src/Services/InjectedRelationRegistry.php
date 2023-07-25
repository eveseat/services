<?php

namespace Seat\Services\Services;

class InjectedRelationRegistry
{
    /**
     * @var array<string, string>
     */
    private array $relations = [];

    private function getExtensionPointKey(string $model, string $extension_name): string {
        return "$model:$extension_name";
    }

    public function registerRelation(string $model, string $extension, string $extension_name): void
    {
        $this->relations[$this->getExtensionPointKey($model, $extension_name)] = $extension;
    }

    public function getRelation(string $model, string $extension_name): ?string
    {
        return $this->relations[$this->getExtensionPointKey($model, $extension_name)] ?? null;
    }
}