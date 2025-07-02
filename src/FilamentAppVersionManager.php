<?php

namespace Alareqi\FilamentAppVersionManager;

class FilamentAppVersionManager
{
    public function version(): string
    {
        return '1.0.0';
    }

    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        // Try to get configuration from plugin first, then fall back to config file
        $plugin = $this->getPlugin();

        if ($plugin) {
            return $plugin->getConfig($key, $default);
        }

        // Fallback to direct config access if plugin is not available
        if ($key === null) {
            return config('filament-app-version-manager');
        }

        return config("filament-app-version-manager.{$key}", $default);
    }

    /**
     * Get the plugin instance if available
     */
    protected function getPlugin(): ?FilamentAppVersionManagerPlugin
    {
        try {
            // Try to get the plugin from Filament
            if (function_exists('filament') && app()->bound('filament')) {
                return FilamentAppVersionManagerPlugin::get();
            }
        } catch (\Exception) {
            // Plugin not registered or Filament not available, fall back to config
        }

        return null;
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
