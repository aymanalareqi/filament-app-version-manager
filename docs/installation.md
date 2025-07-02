# Installation Guide

This guide will walk you through the complete installation process for the Filament App Version Manager package.

## 📋 Prerequisites

Before installing the package, ensure your system meets the following requirements:

### System Requirements
- **PHP**: 8.1 or higher
- **Laravel**: 10.0 or higher  
- **Filament**: 3.0 or higher
- **Database**: MySQL 5.7+ or PostgreSQL 10+

### Optional Requirements
- **Redis**: For improved caching performance (recommended for production)
- **Composer**: Latest version recommended

## 🚀 Installation Steps

### Step 1: Install the Package

Install the package via Composer:

```bash
composer require alareqi/filament-app-version-manager
```

### Step 2: Publish Configuration

Publish the configuration file to customize the package settings:

```bash
php artisan vendor:publish --tag="filament-app-version-manager-config"
```

This will create a configuration file at `config/filament-app-version-manager.php`.

### Step 3: Publish and Run Migrations

Publish the database migrations:

```bash
php artisan vendor:publish --tag="filament-app-version-manager-migrations"
```

Run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

### Step 4: Publish Translations (Optional)

If you want to customize the translations or add support for additional languages:

```bash
php artisan vendor:publish --tag="filament-app-version-manager-translations"
```

This will publish translation files to `resources/lang/vendor/filament-app-version-manager/`.

### Step 5: Register the Plugin

Add the plugin to your Filament panel provider (usually `app/Providers/Filament/AdminPanelProvider.php`):

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentAppVersionManagerPlugin::make(),
            ]);
    }
}
```

### Step 6: Quick Setup Command (Optional)

For a guided installation experience, run the setup command:

```bash
php artisan filament-app-version-manager:install
```

This interactive command will:
- Guide you through the installation process
- Provide helpful tips and next steps
- Verify your installation

## 🔧 Post-Installation Configuration

### Basic Configuration

After installation, you may want to customize the basic settings in `config/filament-app-version-manager.php`:

```php
return [
    'navigation' => [
        'group' => 'App Management',  // Navigation group name
        'sort' => 10,                 // Sort order
        'icon' => 'heroicon-o-rocket-launch', // Navigation icon
    ],
    
    'api' => [
        'enabled' => true,            // Enable API endpoints
        'prefix' => 'api/version',    // API route prefix
        'cache_ttl' => 3600,         // Cache TTL in seconds
    ],
    
    'localization' => [
        'supported_locales' => ['en', 'ar'], // Supported languages
        'default_locale' => 'en',            // Default language
    ],
];
```

### Advanced Configuration

For advanced configuration options, see the [Configuration Guide](configuration.md).

## ✅ Verification

To verify your installation is working correctly:

1. **Check the Admin Panel**: Visit your Filament admin panel and look for "App Versions" in the navigation
2. **Test API Endpoints**: If enabled, test the API endpoint at `/api/version/check`
3. **Run Tests**: Execute the package tests to ensure everything is working

### Testing the Installation

Create a test version to verify everything is working:

```php
use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

AppVersion::create([
    'version' => '1.0.0',
    'platform' => Platform::ALL,
    'release_date' => now(),
    'download_url' => 'https://example.com/download',
    'release_notes' => [
        'en' => 'Initial release',
        'ar' => 'الإصدار الأولي'
    ],
    'is_active' => true,
]);
```

## 🚨 Troubleshooting

### Common Issues

#### Plugin Not Appearing
If the plugin doesn't appear in your admin panel:

1. Clear your application cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. Ensure the plugin is properly registered in your panel provider

#### Migration Errors
If you encounter migration errors:

1. Check your database connection
2. Ensure you have proper database permissions
3. Verify the database supports the required features (JSON columns, etc.)

#### API Not Working
If API endpoints are not accessible:

1. Ensure API routes are enabled in configuration
2. Clear route cache: `php artisan route:clear`
3. Check your web server configuration

For more troubleshooting tips, see the [Troubleshooting Guide](troubleshooting.md).

## 🎉 Next Steps

Now that you have successfully installed the package, you can:

1. **Configure the Package**: Customize settings in the configuration file
2. **Create Your First Version**: Add app versions through the admin panel
3. **Set Up API Integration**: Configure your mobile apps to use the API endpoints
4. **Explore Advanced Features**: Learn about fluent API, closures, and multilingual support

Continue with the [Quick Start Guide](quick-start.md) to begin using the package effectively.

## 📚 Additional Resources

- [Configuration Guide](configuration.md) - Complete configuration reference
- [API Integration](api-integration.md) - Setting up API endpoints
- [Fluent API](fluent-api.md) - Advanced configuration methods
- [Examples](../examples/) - Practical configuration examples
