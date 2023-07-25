<?php

namespace Seat\Services\Services;

class ModelExtensionRegistry
{
    /**
     * @var array<string, string>
     */
    private array $extensions = [];

    private function getExtensionPointKey(string $model, string $extension_name): string {
        return "$model:$extension_name";
    }

    public function registerExtension(string $model, string $extension, string $extension_name): void
    {
        $this->extensions[$this->getExtensionPointKey($model, $extension_name)] = $extension;
    }

    public function getExtension(string $model, string $extension_name): ?string
    {
        return $this->extensions[$this->getExtensionPointKey($model, $extension_name)] ?? null;
    }
}