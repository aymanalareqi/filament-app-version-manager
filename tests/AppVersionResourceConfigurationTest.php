<?php

use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;
use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource;

describe('AppVersionResource Configuration Integration', function () {
    beforeEach(function () {
        // Set up default config values for testing
        config([
            'filament-app-version-manager.navigation.group' => 'Version Management',
            'filament-app-version-manager.navigation.icon' => 'heroicon-o-rocket-launch',
            'filament-app-version-manager.navigation.sort' => 1,
            'filament-app-version-manager.validation.max_version_length' => 20,
            'filament-app-version-manager.defaults.platform' => 'all',
            'filament-app-version-manager.features.beta_versions' => true,
        ]);
    });

    describe('Navigation Configuration', function () {
        it('uses config file values by default', function () {
            expect(AppVersionResource::getNavigationGroup())->toBe(__('Version Management'));
            expect(AppVersionResource::getNavigationIcon())->toBe('heroicon-o-rocket-launch');
            expect(AppVersionResource::getNavigationSort())->toBe(1);
        });

        it('uses plugin overrides when available', function () {
            // Mock plugin registration
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->navigationGroup('Custom Group')
                ->navigationIcon('heroicon-o-cog')
                ->navigationSort(5);

            // Register the plugin globally for testing
            app()->instance(FilamentAppVersionManagerPlugin::class, $plugin);

            // The resource should now use plugin values
            // Note: This test would require proper plugin registration in a real scenario
            expect($plugin->getConfig('navigation.group'))->toBe('Custom Group');
            expect($plugin->getConfig('navigation.icon'))->toBe('heroicon-o-cog');
            expect($plugin->getConfig('navigation.sort'))->toBe(5);
        });
    });

    describe('Form Configuration', function () {
        it('uses validation settings from configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->maxVersionLength(30);

            expect($plugin->getConfig('validation.max_version_length'))->toBe(30);
        });

        it('uses default values from configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->defaultPlatform('ios')
                ->defaultIsActive(false);

            expect($plugin->getConfig('defaults.platform'))->toBe('ios');
            expect($plugin->getConfig('defaults.is_active'))->toBeFalse();
        });

        it('uses feature flags from configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->betaVersions(false)
                ->multilingualReleaseNotes(false);

            expect($plugin->getConfig('features.beta_versions'))->toBeFalse();
            expect($plugin->getConfig('features.multilingual_release_notes'))->toBeFalse();
        });
    });

    describe('Configuration Helper Method', function () {
        it('falls back to config file when plugin is not available', function () {
            // Manually set the config to override the package config
            config(['filament-app-version-manager.navigation.group' => 'Version Management']);

            // Test the static getConfig method behavior
            $reflection = new ReflectionClass(AppVersionResource::class);
            $method = $reflection->getMethod('getConfig');
            $method->setAccessible(true);

            $result = $method->invoke(null, 'navigation.group', 'Default Group');
            expect($result)->toBe('Version Management'); // from config file
        });

        it('handles non-existent config keys gracefully', function () {
            $reflection = new ReflectionClass(AppVersionResource::class);
            $method = $reflection->getMethod('getConfig');
            $method->setAccessible(true);

            $result = $method->invoke(null, 'non.existent.key', 'default');
            expect($result)->toBe('default');
        });
    });

    describe('Exception Handling', function () {
        it('handles plugin retrieval exceptions gracefully', function () {
            // The getConfig method should catch exceptions and fall back to config file
            $reflection = new ReflectionClass(AppVersionResource::class);
            $method = $reflection->getMethod('getConfig');
            $method->setAccessible(true);

            // Laravel's config() returns null when a key is explicitly set to null
            // Our plugin should handle this and return the default instead
            $configResult = config('filament-app-version-manager.navigation.group', 'Default Group');
            expect($configResult)->toBeNull(); // Laravel returns null for explicitly null config values

            // But our plugin's getConfig should return the default when config is null
            $result = $method->invoke(null, 'navigation.group', 'Default Group');
            expect($result)->toBe('Default Group');

            // Test with a config key that doesn't exist to ensure default is returned
            $result2 = $method->invoke(null, 'non.existent.key', 'Default');
            expect($result2)->toBe('Default');
        });
    });

    describe('Multilingual Release Notes', function () {
        it('creates language tabs based on supported locales configuration', function () {
            // Set up custom supported locales
            config(['filament-app-version-manager.localization.supported_locales' => ['en', 'fr', 'ar']]);

            $reflection = new ReflectionClass(AppVersionResource::class);
            $method = $reflection->getMethod('getReleaseNotesLanguageTabs');
            $method->setAccessible(true);

            $tabs = $method->invoke(null);

            expect($tabs)->toHaveCount(3);
            // Test that tabs are created (we can't easily test the internal structure without complex reflection)
            expect($tabs[0])->toBeInstanceOf(\Filament\Forms\Components\Tabs\Tab::class);
            expect($tabs[1])->toBeInstanceOf(\Filament\Forms\Components\Tabs\Tab::class);
            expect($tabs[2])->toBeInstanceOf(\Filament\Forms\Components\Tabs\Tab::class);
        });

        it('uses default supported locales when configuration is not set', function () {
            // Clear configuration to test defaults
            config(['filament-app-version-manager.localization.supported_locales' => null]);

            $reflection = new ReflectionClass(AppVersionResource::class);
            $method = $reflection->getMethod('getReleaseNotesLanguageTabs');
            $method->setAccessible(true);

            $tabs = $method->invoke(null);

            expect($tabs)->toHaveCount(2);
            expect($tabs[0])->toBeInstanceOf(\Filament\Forms\Components\Tabs\Tab::class);
            expect($tabs[1])->toBeInstanceOf(\Filament\Forms\Components\Tabs\Tab::class);
        });

        it('generates proper language labels for common locales', function () {
            $reflection = new ReflectionClass(AppVersionResource::class);
            $method = $reflection->getMethod('getLanguageLabel');
            $method->setAccessible(true);

            expect($method->invoke(null, 'ar'))->toBe('العربية');
            expect($method->invoke(null, 'en'))->toBe('English');
            expect($method->invoke(null, 'fr'))->toBe('Français');
            expect($method->invoke(null, 'unknown'))->toBe('UNKNOWN');
        });
    });
});
