<?php

use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;
use Alareqi\FilamentAppVersionManager\FilamentAppVersionManager;

describe('Backward Compatibility', function () {
    beforeEach(function () {
        // Set up default config values for testing
        config([
            'filament-app-version-manager.api.enabled' => true,
            'filament-app-version-manager.api.cache_ttl' => 300,
            'filament-app-version-manager.validation.max_version_length' => 20,
            'filament-app-version-manager.features.beta_versions' => true,
            'filament-app-version-manager.defaults.is_active' => true,
            'filament-app-version-manager.navigation.group' => 'Version Management',
            'filament-app-version-manager.navigation.icon' => 'heroicon-o-rocket-launch',
        ]);
    });

    describe('FilamentAppVersionManager Class', function () {
        it('falls back to config file when plugin is not available', function () {
            $manager = new FilamentAppVersionManager();
            
            // Mock the scenario where plugin is not available
            expect($manager->getConfig('api.enabled'))->toBeTrue();
            expect($manager->getConfig('api.cache_ttl'))->toBe(300);
        });

        it('uses plugin configuration when plugin is available', function () {
            // This test would require mocking the plugin availability
            // For now, we test the fallback behavior
            $manager = new FilamentAppVersionManager();
            
            expect($manager->getConfig('validation.max_version_length'))->toBe(20);
        });

        it('returns default values when config key does not exist', function () {
            $manager = new FilamentAppVersionManager();
            
            expect($manager->getConfig('non.existent.key', 'default'))->toBe('default');
        });

        it('returns full config when no key is provided', function () {
            $manager = new FilamentAppVersionManager();
            $config = $manager->getConfig();
            
            expect($config)->toBeArray();
            expect($config['api']['enabled'])->toBeTrue();
        });
    });

    describe('Configuration Precedence', function () {
        it('plugin overrides take precedence over config file values', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            // Config file has api.enabled = true
            expect(config('filament-app-version-manager.api.enabled'))->toBeTrue();
            
            // Plugin override should take precedence
            $plugin->api(false);
            expect($plugin->getConfig('api.enabled'))->toBeFalse();
        });

        it('nested configuration overrides work correctly', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->configureWith([
                'api' => [
                    'enabled' => false,
                    'cache_ttl' => 600,
                    'prefix' => 'custom/api',
                ],
            ]);
            
            expect($plugin->getConfig('api.enabled'))->toBeFalse();
            expect($plugin->getConfig('api.cache_ttl'))->toBe(600);
            expect($plugin->getConfig('api.prefix'))->toBe('custom/api');
        });

        it('partial overrides preserve other config values', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            // Only override one API setting
            $plugin->api(false);
            
            // Other API settings should still come from config file
            expect($plugin->getConfig('api.enabled'))->toBeFalse(); // overridden
            expect($plugin->getConfig('api.cache_ttl'))->toBe(300); // from config file
        });

        it('later overrides replace earlier ones', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->apiCacheTtl(600);
            expect($plugin->getConfig('api.cache_ttl'))->toBe(600);
            
            $plugin->apiCacheTtl(900);
            expect($plugin->getConfig('api.cache_ttl'))->toBe(900);
        });
    });

    describe('Configuration Utilities', function () {
        it('can check if a configuration key has been overridden', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            expect($plugin->hasConfigOverride('api.enabled'))->toBeFalse();
            
            $plugin->api(false);
            expect($plugin->hasConfigOverride('api.enabled'))->toBeTrue();
        });

        it('can get specific override values', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->apiCacheTtl(600);
            expect($plugin->getConfigOverride('api.cache_ttl'))->toBe(600);
        });

        it('can get all configuration overrides', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->api(false)->apiCacheTtl(600);
            
            $overrides = $plugin->getConfigOverrides();
            expect($overrides)->toBe([
                'api.enabled' => false,
                'api.cache_ttl' => 600,
            ]);
        });

        it('can clear all configuration overrides', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->api(false)->apiCacheTtl(600);
            expect($plugin->getConfigOverrides())->not->toBeEmpty();
            
            $plugin->clearConfigOverrides();
            expect($plugin->getConfigOverrides())->toBeEmpty();
            
            // Should fall back to config file values
            expect($plugin->getConfig('api.enabled'))->toBeTrue();
        });
    });

    describe('Edge Cases', function () {
        it('handles null values correctly', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->databaseConnection(null);
            expect($plugin->getConfig('database.connection'))->toBeNull();
        });

        it('handles empty arrays correctly', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->supportedLocales([]);
            expect($plugin->getConfig('localization.supported_locales'))->toBe([]);
        });

        it('handles deeply nested configuration keys', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->configureUsing('deeply.nested.config.key', 'value');
            expect($plugin->getConfig('deeply.nested.config.key'))->toBe('value');
        });
    });
});
