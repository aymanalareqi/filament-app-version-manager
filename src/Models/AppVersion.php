<?php

namespace Alareqi\FilamentAppVersionManager\Models;

use Alareqi\FilamentAppVersionManager\Enums\Platform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AppVersion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'app_versions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'version',
        'build_number',
        'platform',
        'minimum_required_version',
        'release_notes',
        'release_date',
        'download_url',
        'force_update',
        'is_active',
        'is_beta',
        'is_rollback',
        'metadata',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'platform' => Platform::class,
        'release_date' => 'date',
        'force_update' => 'boolean',
        'is_active' => 'boolean',
        'is_beta' => 'boolean',
        'is_rollback' => 'boolean',
        'release_notes' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set created_by and updated_by
        static::creating(function ($model) {
            if (auth()->guard('admin')->check()) {
                $model->created_by = auth()->guard('admin')->id();
                $model->updated_by = auth()->guard('admin')->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->guard('admin')->check()) {
                $model->updated_by = auth()->guard('admin')->id();
            }
        });
    }

    /**
     * Get the table name from configuration.
     */
    public function getTable()
    {
        return config('filament-app-version-manager.database.table_name', 'app_versions');
    }

    /**
     * Get the database connection from configuration.
     */
    public function getConnectionName()
    {
        return config('filament-app-version-manager.database.connection') ?? parent::getConnectionName();
    }

    /**
     * Relationship: Admin who created this version.
     */
    public function creator(): BelongsTo
    {
        // Try to get the admin model from the main app
        $adminModel = class_exists('App\\Models\\Admin') ? 'App\\Models\\Admin' : null;

        if ($adminModel) {
            return $this->belongsTo($adminModel, 'created_by');
        }

        // Fallback to a generic relationship
        return $this->belongsTo(config('auth.providers.admins.model', 'App\\Models\\Admin'), 'created_by');
    }

    /**
     * Relationship: Admin who last updated this version.
     */
    public function updater(): BelongsTo
    {
        // Try to get the admin model from the main app
        $adminModel = class_exists('App\\Models\\Admin') ? 'App\\Models\\Admin' : null;

        if ($adminModel) {
            return $this->belongsTo($adminModel, 'updated_by');
        }

        // Fallback to a generic relationship
        return $this->belongsTo(config('auth.providers.admins.model', 'App\\Models\\Admin'), 'updated_by');
    }

    /**
     * Scope: Get active versions only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get versions for a specific platform.
     */
    public function scopeForPlatform(Builder $query, Platform|string $platform): Builder
    {
        $platformValue = $platform instanceof Platform ? $platform->value : $platform;

        return $query->where(function ($q) use ($platformValue) {
            $q->where('platform', $platformValue)
                ->orWhere('platform', Platform::ALL->value);
        });
    }

    /**
     * Scope: Get non-beta versions only.
     */
    public function scopeStable(Builder $query): Builder
    {
        return $query->where('is_beta', false);
    }

    /**
     * Scope: Get latest version for a platform.
     */
    public function scopeLatestForPlatform(Builder $query, Platform|string $platform): Builder
    {
        return $query->forPlatform($platform)
            ->active()
            ->stable()
            ->orderByDesc('release_date')
            ->orderByDesc('id');
    }

    /**
     * Get release notes attribute (decode JSON).
     */
    public function getReleaseNotesAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Get localized release notes for a specific locale.
     * Falls back to default locale, then to first available translation.
     * Returns array of all translations if no locale specified (backward compatibility).
     */
    public function getLocalizedReleaseNotes(?string $locale = null): array|string|null
    {
        $releaseNotes = $this->release_notes;

        // If release_notes is null or empty, return null
        if (empty($releaseNotes) || !is_array($releaseNotes)) {
            return null;
        }

        // If no locale specified, return all translations (backward compatibility)
        if (is_null($locale)) {
            return $releaseNotes;
        }

        // Try requested locale first
        if (isset($releaseNotes[$locale]) && !empty($releaseNotes[$locale])) {
            return $releaseNotes[$locale];
        }

        // Fall back to default locale from configuration
        $defaultLocale = config('filament-app-version-manager.localization.default_locale', 'en');
        if (isset($releaseNotes[$defaultLocale]) && !empty($releaseNotes[$defaultLocale])) {
            return $releaseNotes[$defaultLocale];
        }

        // Fall back to fallback locale from configuration
        $fallbackLocale = config('filament-app-version-manager.localization.fallback_locale', 'en');
        if ($fallbackLocale !== $defaultLocale && isset($releaseNotes[$fallbackLocale]) && !empty($releaseNotes[$fallbackLocale])) {
            return $releaseNotes[$fallbackLocale];
        }

        // Return first available translation
        $firstAvailable = array_values(array_filter($releaseNotes));
        return !empty($firstAvailable) ? $firstAvailable[0] : null;
    }

    /**
     * Get platform display name.
     */
    public function getPlatformNameAttribute(): string
    {
        return $this->platform->getLabel();
    }

    /**
     * Check if this version is newer than another version.
     */
    public function isNewerThan(string $version): bool
    {
        return version_compare($this->version, $version, '>');
    }

    /**
     * Check if this version is older than another version.
     */
    public function isOlderThan(string $version): bool
    {
        return version_compare($this->version, $version, '<');
    }

    /**
     * Check if this version requires a force update from another version.
     */
    public function requiresForceUpdateFrom(string $version): bool
    {
        if (!$this->force_update) {
            return false;
        }

        return $this->isNewerThan($version);
    }

    /**
     * Validate semantic versioning format.
     */
    public static function validateSemanticVersion(string $version): bool
    {
        if (!config('filament-app-version-manager.validation.semantic_versioning', true)) {
            return true;
        }

        $pattern = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

        return preg_match($pattern, $version) === 1;
    }

    /**
     * Get the latest version for a specific platform.
     */
    public static function getLatestForPlatform(Platform|string $platform): ?self
    {
        return static::latestForPlatform($platform)->first();
    }

    /**
     * Check if an update is available for a given version and platform.
     */
    public static function isUpdateAvailable(string $currentVersion, Platform|string $platform, ?string $locale = null): array
    {
        $latestVersion = static::getLatestForPlatform($platform);

        if (!$latestVersion) {
            return [
                'update_available' => false,
                'latest_version' => null,
                'force_update' => false,
                'release_notes' => null,
                'download_url' => null,
                'release_date' => null,
            ];
        }

        $updateAvailable = $latestVersion->isNewerThan($currentVersion);
        $forceUpdate = $latestVersion->requiresForceUpdateFrom($currentVersion);

        // Get localized release notes based on requested locale
        $releaseNotes = $latestVersion->getLocalizedReleaseNotes($locale);

        return [
            'update_available' => $updateAvailable,
            'latest_version' => $latestVersion->version,
            'force_update' => $forceUpdate,
            'release_notes' => $releaseNotes,
            'download_url' => $latestVersion->download_url,
            'release_date' => $latestVersion->release_date->toDateString(),
        ];
    }
}
