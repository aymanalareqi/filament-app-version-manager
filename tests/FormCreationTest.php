<?php

use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;
use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource;

describe('Form Configuration Evaluation', function () {
    beforeEach(function () {
        // Set up default config values for testing
        config([
            'filament-app-version-manager.validation.max_version_length' => 20,
            'filament-app-version-manager.defaults.platform' => 'ios',
            'filament-app-version-manager.defaults.is_active' => true,
            'filament-app-version-manager.localization.supported_locales' => ['ar', 'en'],
            'filament-app-version-manager.features.multilingual_release_notes' => false,
        ]);
    });

    it('evaluates closures correctly when plugin configuration contains closures', function () {
        // Test that closures in plugin configuration are properly evaluated
        // and don't cause issues in form components
        $plugin = FilamentAppVersionManagerPlugin::make()
            ->maxVersionLength(fn() => 30)
            ->defaultPlatform(fn() => 'android');

        // Test that the plugin evaluates closures correctly
        expect($plugin->getConfig('validation.max_version_length'))->toBe(30);
        expect($plugin->getConfig('defaults.platform'))->toBe('android');

        // Ensure they are not closures
        expect($plugin->getConfig('validation.max_version_length'))->not->toBeInstanceOf(\Closure::class);
        expect($plugin->getConfig('defaults.platform'))->not->toBeInstanceOf(\Closure::class);
    });

    it('handles mixed primitive and closure configuration', function () {
        $plugin = FilamentAppVersionManagerPlugin::make()
            ->maxVersionLength(25) // primitive value
            ->defaultPlatform(fn() => 'ios') // closure value
            ->defaultIsActive(true); // primitive value

        expect($plugin->getConfig('validation.max_version_length'))->toBe(25);
        expect($plugin->getConfig('defaults.platform'))->toBe('ios');
        expect($plugin->getConfig('defaults.is_active'))->toBeTrue();

        // Ensure none are closures
        expect($plugin->getConfig('validation.max_version_length'))->not->toBeInstanceOf(\Closure::class);
        expect($plugin->getConfig('defaults.platform'))->not->toBeInstanceOf(\Closure::class);
        expect($plugin->getConfig('defaults.is_active'))->not->toBeInstanceOf(\Closure::class);
    });

    it('falls back to config file when plugin is not available', function () {
        // Test the AppVersionResource::getConfig method fallback behavior
        $maxLength = AppVersionResource::getConfig('validation.max_version_length', 20);
        $defaultPlatform = AppVersionResource::getConfig('defaults.platform', 'ios');

        expect($maxLength)->toBe(20);
        expect($defaultPlatform)->toBe('ios');
    });

    it('handles nested closure evaluation', function () {
        $plugin = FilamentAppVersionManagerPlugin::make()
            ->supportedLocales(fn() => ['en', 'ar', 'fr'])
            ->multilingualReleaseNotes(fn() => true);

        $supportedLocales = $plugin->getConfig('localization.supported_locales');
        $multilingualEnabled = $plugin->getConfig('features.multilingual_release_notes');

        expect($supportedLocales)->toBe(['en', 'ar', 'fr']);
        expect($multilingualEnabled)->toBeTrue();

        // Ensure they are not closures
        expect($supportedLocales)->not->toBeInstanceOf(\Closure::class);
        expect($multilingualEnabled)->not->toBeInstanceOf(\Closure::class);
    });

    it('ensures AppVersionResource getConfig method evaluates closures', function () {
        // This test specifically verifies that the fix for the closure evaluation error works
        // The main fix is that AppVersionResource::getConfig now checks if a value is a closure
        // and evaluates it before returning, preventing Filament form components from receiving
        // unevaluated closures that would cause the "$attribute is unresolvable" error

        // Test with config file fallback (the most common scenario)
        $result = AppVersionResource::getConfig('validation.max_version_length', 25);

        expect($result)->toBe(20); // Should return the config value, not the default
        expect($result)->not->toBeInstanceOf(\Closure::class);

        // Test with a closure as default value to ensure it gets evaluated
        $closureDefault = fn() => 42;
        $result2 = AppVersionResource::getConfig('non.existent.key', $closureDefault);

        expect($result2)->toBe(42);
        expect($result2)->not->toBeInstanceOf(\Closure::class);
    });
});
