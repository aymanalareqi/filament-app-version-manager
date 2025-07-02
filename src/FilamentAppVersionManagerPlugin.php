<?php

namespace Alareqi\FilamentAppVersionManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource;

class FilamentAppVersionManagerPlugin implements Plugin
{
    protected bool $hasApiRoutes = true;

    protected ?string $navigationGroup = null;

    protected ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected ?int $navigationSort = null;

    /**
     * Configuration overrides that take precedence over config file values
     */
    protected array $configOverrides = [];

    public function getId(): string
    {
        return 'filament-app-version-manager';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                AppVersionResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function hasApiRoutes(bool $condition = true): static
    {
        $this->hasApiRoutes = $condition;

        return $this;
    }

    public function getHasApiRoutes(): bool
    {
        return $this->hasApiRoutes;
    }

    public function navigationGroup(string $group): static
    {
        $this->navigationGroup = $group;
        return $this->configureUsing('navigation.group', $group);
    }

    public function getNavigationGroup(): string
    {
        return $this->getConfig('navigation.group', __('filament-app-version-manager::app_version.navigation_group'));
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;
        return $this->configureUsing('navigation.icon', $icon);
    }

    public function getNavigationIcon(): ?string
    {
        return $this->getConfig('navigation.icon', $this->navigationIcon);
    }

    public function navigationSort(int $sort): static
    {
        $this->navigationSort = $sort;
        return $this->configureUsing('navigation.sort', $sort);
    }

    public function getNavigationSort(): ?int
    {
        return $this->getConfig('navigation.sort', $this->navigationSort);
    }

    /**
     * Override a configuration value
     */
    public function configureUsing(string $key, mixed $value): static
    {
        $this->setNestedValue($this->configOverrides, $key, $value);

        return $this;
    }

    /**
     * Override multiple configuration values
     */
    public function configureWith(array $config): static
    {
        $this->configOverrides = array_merge($this->configOverrides, $config);

        return $this;
    }

    /**
     * Get a configuration value, checking overrides first, then falling back to config file
     */
    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            // Return merged configuration
            return array_merge(
                config('filament-app-version-manager', []),
                $this->configOverrides
            );
        }

        // Check for override first
        if ($this->hasConfigOverride($key)) {
            return $this->getConfigOverride($key);
        }

        // Fall back to config file
        return config("filament-app-version-manager.{$key}", $default);
    }

    /**
     * Check if a configuration key has been overridden
     */
    public function hasConfigOverride(string $key): bool
    {
        return $this->hasNestedKey($this->configOverrides, $key);
    }

    /**
     * Get an overridden configuration value
     */
    public function getConfigOverride(string $key, mixed $default = null): mixed
    {
        return $this->getNestedValue($this->configOverrides, $key, $default);
    }

    /**
     * Helper method to check if a nested key exists in an array
     */
    protected function hasNestedKey(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }
            $current = $current[$segment];
        }

        return true;
    }

    /**
     * Helper method to get a nested value from an array
     */
    protected function getNestedValue(array $array, string $key): mixed
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * Helper method to set a nested value in an array
     */
    protected function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current = $value;
    }

    // ========================================
    // Fluent API Configuration Methods
    // ========================================

    /**
     * Configure API settings
     */
    public function api(bool $enabled = true): static
    {
        return $this->configureUsing('api.enabled', $enabled);
    }

    /**
     * Configure API prefix
     */
    public function apiPrefix(string $prefix): static
    {
        return $this->configureUsing('api.prefix', $prefix);
    }

    /**
     * Configure API middleware
     */
    public function apiMiddleware(array $middleware): static
    {
        return $this->configureUsing('api.middleware', $middleware);
    }

    /**
     * Configure API cache TTL
     */
    public function apiCacheTtl(int $ttl): static
    {
        return $this->configureUsing('api.cache_ttl', $ttl);
    }

    /**
     * Enable or disable API stats endpoint
     */
    public function apiStats(bool $enabled = true): static
    {
        return $this->configureUsing('api.enable_stats', $enabled);
    }

    /**
     * Configure validation rules
     */
    public function validation(array $rules): static
    {
        return $this->configureWith(['validation' => $rules]);
    }

    /**
     * Configure semantic versioning validation
     */
    public function semanticVersioning(bool $enabled = true): static
    {
        return $this->configureUsing('validation.semantic_versioning', $enabled);
    }

    /**
     * Configure maximum version length
     */
    public function maxVersionLength(int $length): static
    {
        return $this->configureUsing('validation.max_version_length', $length);
    }

    /**
     * Configure maximum build number length
     */
    public function maxBuildNumberLength(int $length): static
    {
        return $this->configureUsing('validation.max_build_number_length', $length);
    }

    /**
     * Configure maximum download URL length
     */
    public function maxDownloadUrlLength(int $length): static
    {
        return $this->configureUsing('validation.max_download_url_length', $length);
    }

    /**
     * Configure default values
     */
    public function defaults(array $defaults): static
    {
        return $this->configureWith(['defaults' => $defaults]);
    }

    /**
     * Configure default platform
     */
    public function defaultPlatform(string $platform): static
    {
        return $this->configureUsing('defaults.platform', $platform);
    }

    /**
     * Configure default active state
     */
    public function defaultIsActive(bool $active = true): static
    {
        return $this->configureUsing('defaults.is_active', $active);
    }

    /**
     * Configure default beta state
     */
    public function defaultIsBeta(bool $beta = false): static
    {
        return $this->configureUsing('defaults.is_beta', $beta);
    }

    /**
     * Configure default rollback state
     */
    public function defaultIsRollback(bool $rollback = false): static
    {
        return $this->configureUsing('defaults.is_rollback', $rollback);
    }

    /**
     * Configure default force update state
     */
    public function defaultForceUpdate(bool $forceUpdate = false): static
    {
        return $this->configureUsing('defaults.force_update', $forceUpdate);
    }

    /**
     * Configure features
     */
    public function features(array $features): static
    {
        return $this->configureWith(['features' => $features]);
    }

    /**
     * Enable or disable multilingual release notes
     */
    public function multilingualReleaseNotes(bool $enabled = true): static
    {
        return $this->configureUsing('features.multilingual_release_notes', $enabled);
    }

    /**
     * Enable or disable version rollback
     */
    public function versionRollback(bool $enabled = true): static
    {
        return $this->configureUsing('features.version_rollback', $enabled);
    }

    /**
     * Enable or disable beta versions
     */
    public function betaVersions(bool $enabled = true): static
    {
        return $this->configureUsing('features.beta_versions', $enabled);
    }

    /**
     * Enable or disable force updates
     */
    public function forceUpdates(bool $enabled = true): static
    {
        return $this->configureUsing('features.force_updates', $enabled);
    }

    /**
     * Enable or disable metadata storage
     */
    public function metadataStorage(bool $enabled = true): static
    {
        return $this->configureUsing('features.metadata_storage', $enabled);
    }

    /**
     * Enable or disable audit trail
     */
    public function auditTrail(bool $enabled = true): static
    {
        return $this->configureUsing('features.audit_trail', $enabled);
    }

    /**
     * Configure supported platforms
     */
    public function platforms(array $platforms): static
    {
        return $this->configureWith(['platforms' => $platforms]);
    }

    /**
     * Add a platform configuration
     */
    public function addPlatform(string $key, array $config): static
    {
        return $this->configureUsing("platforms.{$key}", $config);
    }

    /**
     * Configure database settings
     */
    public function database(array $config): static
    {
        return $this->configureWith(['database' => $config]);
    }

    /**
     * Configure database table name
     */
    public function tableName(string $tableName): static
    {
        return $this->configureUsing('database.table_name', $tableName);
    }

    /**
     * Configure database connection
     */
    public function databaseConnection(?string $connection): static
    {
        return $this->configureUsing('database.connection', $connection);
    }

    /**
     * Configure localization settings
     */
    public function localization(array $config): static
    {
        return $this->configureWith(['localization' => $config]);
    }

    /**
     * Configure default locale
     */
    public function defaultLocale(string $locale): static
    {
        return $this->configureUsing('localization.default_locale', $locale);
    }

    /**
     * Configure supported locales
     */
    public function supportedLocales(array $locales): static
    {
        return $this->configureUsing('localization.supported_locales', $locales);
    }

    /**
     * Configure fallback locale
     */
    public function fallbackLocale(string $locale): static
    {
        return $this->configureUsing('localization.fallback_locale', $locale);
    }



    /**
     * Get all configuration overrides
     */
    public function getConfigOverrides(): array
    {
        $flattened = [];
        $this->flattenArray($this->configOverrides, $flattened);
        return $flattened;
    }

    /**
     * Flatten a nested array with dot notation keys
     */
    private function flattenArray(array $array, array &$result, string $prefix = ''): void
    {
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value) && !empty($value) && array_keys($value) !== range(0, count($value) - 1)) {
                $this->flattenArray($value, $result, $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }
    }

    /**
     * Clear all configuration overrides
     */
    public function clearConfigOverrides(): static
    {
        $this->configOverrides = [];

        return $this;
    }
}
