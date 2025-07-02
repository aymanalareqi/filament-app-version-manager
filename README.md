# Filament App Version Manager

A comprehensive Filament plugin for managing mobile app versions with bilingual support, API endpoints, and advanced features.

## Features

- 🚀 **Complete Version Management**: Create, edit, and manage app versions for iOS, Android, and cross-platform releases
- 🌍 **Bilingual Support**: Full Arabic and English localization with JSON multilingual fields
- 📱 **Platform Support**: iOS, Android, and All platforms with Filament enum integration
- 🔄 **Version Rollback**: Built-in rollback functionality with proper validation
- 📊 **API Integration**: RESTful API endpoints for version checking with caching and rate limiting
- ⚡ **Force Updates**: Configure mandatory updates for critical releases
- 🧪 **Beta Versions**: Support for beta releases and testing
- 📈 **Metadata Support**: Store additional version metadata as JSON
- 🔒 **Audit Trail**: Track who created and updated versions
- 🎨 **Modern UI**: Beautiful Filament interface with tabs, actions, and notifications

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+
- Filament 3.0+

## Installation

### 1. Install via Composer

```bash
composer require alareqi/filament-app-version-manager
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag="filament-app-version-manager-config"
```

### 3. Publish and Run Migrations

```bash
php artisan vendor:publish --tag="filament-app-version-manager-migrations"
php artisan migrate
```

### 4. Publish Translations (Optional)

```bash
php artisan vendor:publish --tag="filament-app-version-manager-translations"
```

### 5. Register the Plugin

Add the plugin to your Filament panel provider:

```php
use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugins([
            FilamentAppVersionManagerPlugin::make(),
        ]);
}
```

## Configuration

The plugin configuration file is published to `config/filament-app-version-manager.php`. Key configuration options include:

### API Configuration
```php
'api' => [
    'enabled' => true,
    'prefix' => 'api/version',
    'middleware' => ['throttle:60,1'],
    'enable_stats' => false,
],
```

### Navigation
```php
'navigation' => [
    'group' => 'System',
    'sort' => 10,
    'icon' => 'heroicon-o-device-phone-mobile',
],
```

### Features
```php
'features' => [
    'multilingual_release_notes' => true,
    'version_rollback' => true,
    'beta_versions' => true,
    'force_update' => true,
    'metadata_storage' => true,
    'audit_trail' => true,
],
```

## Usage

### Admin Panel

Once installed, you'll find the "App Versions" resource in your Filament admin panel. You can:

- Create new app versions with release notes in multiple languages
- Set platform-specific versions (iOS, Android, All)
- Configure force updates and beta releases
- Manage version rollbacks
- View comprehensive version history

### API Endpoints

#### Version Check
```http
POST /api/version/check
Content-Type: application/json

{
    "platform": "ios",
    "current_version": "1.0.0"
}
```

Response:
```json
{
    "success": true,
    "current_version": "1.0.0",
    "platform": "ios",
    "platform_label": "iOS",
    "update_available": true,
    "latest_version": "1.1.0",
    "force_update": false,
    "download_url": "https://apps.apple.com/app/yourapp",
    "release_date": "2025-07-01",
    "release_notes": {
        "en": "Bug fixes and improvements",
        "ar": "إصلاح الأخطاء والتحسينات"
    },
    "checked_at": "2025-07-01T12:00:00.000000Z"
}
```

#### Stats Endpoint (Optional)
```http
GET /api/version/stats
```

## Customization

### Plugin Configuration

You can customize the plugin behavior using fluent methods:

```php
FilamentAppVersionManagerPlugin::make()
    ->navigationGroup('App Management')
    ->navigationSort(5)
    ->navigationIcon('heroicon-o-rocket-launch')
    ->enableApiRoutes(false) // Disable API routes
```

### Model Relationships

If you have an Admin model, the plugin will automatically create relationships:

```php
// In your Admin model
public function createdAppVersions()
{
    return $this->hasMany(AppVersion::class, 'created_by');
}

public function updatedAppVersions()
{
    return $this->hasMany(AppVersion::class, 'updated_by');
}
```

## Database Schema

The plugin creates an `app_versions` table with the following structure:

- `id` - Primary key
- `version` - Version string (e.g., "1.0.0")
- `build_number` - Optional build number
- `platform` - Enum: ios, android, all
- `minimum_required_version` - Minimum version required
- `release_notes` - JSON multilingual field
- `release_date` - Release date
- `download_url` - App store URL
- `force_update` - Boolean flag
- `is_active` - Boolean flag
- `is_beta` - Boolean flag
- `is_rollback` - Boolean flag
- `metadata` - JSON field for additional data
- `created_by` - Foreign key to admin users
- `updated_by` - Foreign key to admin users
- `created_at` - Timestamp
- `updated_at` - Timestamp

## Translations

The plugin supports full bilingual functionality with translations for:

- English (`en`)
- Arabic (`ar`)

Translation files are located in:
- `lang/vendor/filament-app-version-manager/en/app_version.php`
- `lang/vendor/filament-app-version-manager/ar/app_version.php`

## Testing

The plugin includes comprehensive testing capabilities. You can seed sample data using:

```php
use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

AppVersion::create([
    'version' => '1.0.0',
    'platform' => Platform::IOS,
    'release_date' => now(),
    'download_url' => 'https://apps.apple.com/app/yourapp',
    'release_notes' => [
        'en' => 'Initial release',
        'ar' => 'الإصدار الأولي'
    ]
]);
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Support

For support, please open an issue on the GitHub repository or contact the development team.
