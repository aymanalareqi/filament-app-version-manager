<?php

namespace Alareqi\FilamentAppVersionManager;

class FilamentAppVersionManager
{
    public function version(): string
    {
        return '1.0.0';
    }

    public function getConfig(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('filament-app-version-manager');
        }

        return config("filament-app-version-manager.{$key}", $default);
    }

    public function isApiEnabled(): bool
    {
        return $this->getConfig('api.enabled', true);
    }

    public function getCacheTtl(): int
    {
        return $this->getConfig('api.cache_ttl', 300);
    }

    public function getSupportedPlatforms(): array
    {
        return array_keys($this->getConfig('platforms', []));
    }

    public function getPlatformConfig(string $platform): array
    {
        return $this->getConfig("platforms.{$platform}", []);
    }

    public function isFeatureEnabled(string $feature): bool
    {
        return $this->getConfig("features.{$feature}", false);
    }
}
