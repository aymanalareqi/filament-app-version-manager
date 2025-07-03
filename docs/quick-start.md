# Quick Start Guide

Get up and running with Filament App Version Manager in just a few minutes!

## 🚀 5-Minute Setup

### 1. Install and Configure

```bash
# Install the package
composer require alareqi/filament-app-version-manager

# Publish configuration and migrations
php artisan vendor:publish --tag="filament-app-version-manager-config"
php artisan vendor:publish --tag="filament-app-version-manager-migrations"

# Run migrations
php artisan migrate
```

### 2. Register the Plugin

Add to your Filament panel provider:

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

### 3. Create Your First Version

Visit your Filament admin panel and navigate to "App Versions" to create your first version:

- **Version**: `1.0.0`
- **Platform**: `iOS` or `Android`
- **Release Date**: Today
- **Download URL**: Your app store URL
- **Release Notes**: Add notes in your supported languages
- **Status**: Active

## 📱 Basic Usage Examples

### Creating Versions Programmatically

```php
use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

// Create a basic version
AppVersion::create([
    'version' => '1.0.0',
    'platform' => Platform::IOS,
    'release_date' => now(),
    'download_url' => 'https://apps.apple.com/app/yourapp',
    'release_notes' => [
        'en' => 'Initial release with core features',
        'ar' => 'الإصدار الأولي مع الميزات الأساسية'
    ],
    'is_active' => true,
]);

// Create a force update version
AppVersion::create([
    'version' => '1.1.0',
    'platform' => Platform::ANDROID,
    'release_date' => now(),
    'download_url' => 'https://play.google.com/store/apps/details?id=com.yourapp',
    'release_notes' => [
        'en' => 'Critical security update - please update immediately',
        'ar' => 'تحديث أمني مهم - يرجى التحديث فوراً'
    ],
    'is_active' => true,
    'force_update' => true,
]);
```

### Using the API

Test your API endpoint:

```bash
# Check for updates
curl -X POST http://yourapp.com/api/version/check \
  -H "Content-Type: application/json" \
  -d '{
    "platform": "ios",
    "current_version": "1.0.0",
    "locale": "en"
  }'
```

Expected response (with locale specified):
```json
{
    "success": true,
    "update_available": true,
    "latest_version": "1.1.0",
    "force_update": false,
    "download_url": "https://apps.apple.com/app/yourapp",
    "release_notes": "Bug fixes and improvements"
}
```

Expected response (without locale):
```json
{
    "success": true,
    "update_available": true,
    "latest_version": "1.1.0",
    "force_update": false,
    "download_url": "https://apps.apple.com/app/yourapp",
    "release_notes": {
        "en": "Bug fixes and improvements",
        "ar": "إصلاح الأخطاء والتحسينات"
    }
}
```

## ⚙️ Common Configurations

### Basic Plugin Configuration

```php
FilamentAppVersionManagerPlugin::make()
    ->navigationGroup('App Management')
    ->navigationSort(10)
    ->enableApiRoutes(true)
    ->supportedLocales(['en', 'ar'])
    ->enableBetaVersions(true)
```

### Environment-Based Configuration

```php
FilamentAppVersionManagerPlugin::make()
    ->enableApiRoutes(fn() => !app()->environment('local'))
    ->apiCacheTtl(fn() => config('app.env') === 'production' ? 3600 : 60)
    ->enableBetaVersions(fn() => config('app.debug'))
```

## 🌍 Multilingual Setup

### 1. Configure Supported Locales

```php
// In config/filament-app-version-manager.php
'localization' => [
    'supported_locales' => ['en', 'ar', 'fr', 'es'],
    'default_locale' => 'en',
],
```

### 2. Create Multilingual Release Notes

When creating versions in the admin panel, you'll see tabs for each language:

- **English Tab**: Enter English release notes
- **العربية Tab**: Enter Arabic release notes  
- **Français Tab**: Enter French release notes
- **Español Tab**: Enter Spanish release notes

### 3. API Response Format

**Without locale parameter (returns all languages):**
```json
{
    "release_notes": {
        "en": "Bug fixes and improvements",
        "ar": "إصلاح الأخطاء والتحسينات",
        "fr": "Corrections de bugs et améliorations",
        "es": "Corrección de errores y mejoras"
    }
}
```

**With locale parameter (returns single localized string):**
```json
{
    "release_notes": "Bug fixes and improvements"
}
```

## 🔧 Essential Configuration Options

### API Configuration

```php
'api' => [
    'enabled' => true,                    // Enable API endpoints
    'prefix' => 'api/version',           // API route prefix
    'middleware' => ['throttle:60,1'],   // Rate limiting
    'cache_ttl' => 3600,                 // Cache for 1 hour
],
```

### Feature Flags

```php
'features' => [
    'multilingual_release_notes' => true, // Language tabs
    'version_rollback' => true,           // Rollback functionality
    'beta_versions' => true,              // Beta version support
    'force_update' => true,               // Force update capability
    'metadata_storage' => true,           // JSON metadata field
],
```

### Navigation

```php
'navigation' => [
    'group' => 'App Management',         // Navigation group
    'sort' => 10,                        // Sort order
    'icon' => 'heroicon-o-rocket-launch', // Icon
],
```

## 📊 Testing Your Setup

### 1. Admin Panel Test

1. Go to your Filament admin panel
2. Look for "App Versions" in the navigation
3. Create a new version
4. Verify all fields work correctly
5. Test multilingual release notes

### 2. API Test

```php
// Test in tinker or a controller
use GuzzleHttp\Client;

$client = new Client();
$response = $client->post('http://yourapp.com/api/version/check', [
    'json' => [
        'platform' => 'ios',
        'current_version' => '1.0.0'
    ]
]);

$data = json_decode($response->getBody(), true);
dd($data);
```

### 3. Database Test

```php
// Check if versions are being created
use Alareqi\FilamentAppVersionManager\Models\AppVersion;

$versions = AppVersion::all();
dd($versions->toArray());
```

## 🎯 Next Steps

Now that you have the basics working:

1. **Explore Advanced Features**:
   - [Fluent API Configuration](fluent-api.md)
   - [Closure-Based Configuration](closure-configuration.md)
   - [Custom Platform Support](custom-platforms.md)

2. **Integrate with Your Mobile Apps**:
   - [API Integration Guide](api-integration.md)
   - [Mobile App Examples](../examples/api-usage-examples.php)

3. **Optimize for Production**:
   - [Performance Optimization](performance.md)
   - [Security Best Practices](security.md)
   - [Caching Strategies](performance.md#caching)

4. **Customize the Experience**:
   - [Custom Translations](multilingual.md)
   - [Theme Customization](customization.md)
   - [Advanced Validation](configuration.md#validation)

## 🆘 Need Help?

- **Documentation**: Check the [full documentation](README.md)
- **Examples**: Browse [configuration examples](../examples/)
- **Issues**: Report problems on [GitHub Issues](https://github.com/aymanalareqi/filament-app-version-manager/issues)
- **Discussions**: Join [GitHub Discussions](https://github.com/aymanalareqi/filament-app-version-manager/discussions)

## 🎉 You're Ready!

Congratulations! You now have a fully functional app version management system. Your mobile apps can check for updates, users can manage versions through the beautiful Filament interface, and you have a solid foundation for scaling your version management needs.

Happy coding! 🚀
