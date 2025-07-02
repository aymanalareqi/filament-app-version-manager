<?php

use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

beforeEach(function () {
    // Set up test configuration
    config([
        'filament-app-version-manager.localization.default_locale' => 'en',
        'filament-app-version-manager.localization.fallback_locale' => 'en',
        'filament-app-version-manager.localization.supported_locales' => ['en', 'ar', 'fr', 'es'],
    ]);
});

it('returns localized release notes for requested locale', function () {
    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [
            'en' => 'English release notes',
            'ar' => 'ملاحظات الإصدار بالعربية',
            'fr' => 'Notes de version en français',
        ],
        'is_active' => true,
    ]);

    // Test English
    expect($version->getLocalizedReleaseNotes('en'))->toBe('English release notes');

    // Test Arabic
    expect($version->getLocalizedReleaseNotes('ar'))->toBe('ملاحظات الإصدار بالعربية');

    // Test French
    expect($version->getLocalizedReleaseNotes('fr'))->toBe('Notes de version en français');
});

it('falls back to default locale when requested locale not available', function () {
    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [
            'en' => 'English release notes',
            'ar' => 'ملاحظات الإصدار بالعربية',
        ],
        'is_active' => true,
    ]);

    // Request Spanish (not available), should fall back to English (default)
    expect($version->getLocalizedReleaseNotes('es'))->toBe('English release notes');
});

it('falls back to fallback locale when default locale not available', function () {
    config(['filament-app-version-manager.localization.default_locale' => 'fr']);
    config(['filament-app-version-manager.localization.fallback_locale' => 'en']);

    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [
            'en' => 'English release notes',
            'ar' => 'ملاحظات الإصدار بالعربية',
        ],
        'is_active' => true,
    ]);

    // Request Spanish (not available), default French (not available), should fall back to English
    expect($version->getLocalizedReleaseNotes('es'))->toBe('English release notes');
});

it('returns first available translation when no fallbacks available', function () {
    config(['filament-app-version-manager.localization.default_locale' => 'fr']);
    config(['filament-app-version-manager.localization.fallback_locale' => 'de']);

    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [
            'ar' => 'ملاحظات الإصدار بالعربية',
            'es' => 'Notas de la versión en español',
        ],
        'is_active' => true,
    ]);

    // Request Italian (not available), default French (not available), fallback German (not available)
    // Should return first available (Arabic)
    expect($version->getLocalizedReleaseNotes('it'))->toBe('ملاحظات الإصدار بالعربية');
});

it('returns all translations when no locale specified', function () {
    $releaseNotes = [
        'en' => 'English release notes',
        'ar' => 'ملاحظات الإصدار بالعربية',
        'fr' => 'Notes de version en français',
    ];

    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => $releaseNotes,
        'is_active' => true,
    ]);

    // No locale specified should return all translations (backward compatibility)
    expect($version->getLocalizedReleaseNotes())->toBe($releaseNotes);
    expect($version->getLocalizedReleaseNotes(null))->toBe($releaseNotes);
});

it('returns null when release notes are empty', function () {
    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => null,
        'is_active' => true,
    ]);

    expect($version->getLocalizedReleaseNotes('en'))->toBeNull();
    expect($version->getLocalizedReleaseNotes())->toBeNull();
});

it('returns null when release notes are empty array', function () {
    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [],
        'is_active' => true,
    ]);

    expect($version->getLocalizedReleaseNotes('en'))->toBeNull();
    expect($version->getLocalizedReleaseNotes())->toBeNull();
});

it('ignores empty string translations', function () {
    $version = AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [
            'en' => '',  // Empty string
            'ar' => 'ملاحظات الإصدار بالعربية',
            'fr' => '   ',  // Whitespace only
            'es' => 'Notas de la versión en español',
        ],
        'is_active' => true,
    ]);

    // Request English (empty), should fall back to default (English is default but empty)
    // Should fall back to first non-empty translation
    expect($version->getLocalizedReleaseNotes('en'))->toBe('ملاحظات الإصدار بالعربية');
});

it('handles non array release notes gracefully', function () {
    $version = new AppVersion([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'is_active' => true,
    ]);

    // Manually set release_notes to a string (bypassing normal casting)
    $version->setRawAttributes(['release_notes' => 'Simple string release notes']);

    expect($version->getLocalizedReleaseNotes('en'))->toBeNull();
});

it('returns localized release notes in update check', function () {
    AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [
            'en' => 'English release notes',
            'ar' => 'ملاحظات الإصدار بالعربية',
            'fr' => 'Notes de version en français',
        ],
        'is_active' => true,
    ]);

    // Test with English locale
    $updateInfo = AppVersion::isUpdateAvailable('0.9.0', Platform::IOS, 'en');
    expect($updateInfo['release_notes'])->toBe('English release notes');

    // Test with Arabic locale
    $updateInfo = AppVersion::isUpdateAvailable('0.9.0', Platform::IOS, 'ar');
    expect($updateInfo['release_notes'])->toBe('ملاحظات الإصدار بالعربية');

    // Test with unavailable locale (should fall back to default)
    $updateInfo = AppVersion::isUpdateAvailable('0.9.0', Platform::IOS, 'es');
    expect($updateInfo['release_notes'])->toBe('English release notes');
});

it('maintains backward compatibility when no locale provided', function () {
    $releaseNotes = [
        'en' => 'English release notes',
        'ar' => 'ملاحظات الإصدار بالعربية',
    ];

    AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => $releaseNotes,
        'is_active' => true,
    ]);

    // Test without locale parameter (backward compatibility)
    $updateInfo = AppVersion::isUpdateAvailable('0.9.0', Platform::IOS);
    expect($updateInfo['release_notes'])->toBe($releaseNotes);

    // Test with null locale
    $updateInfo = AppVersion::isUpdateAvailable('0.9.0', Platform::IOS, null);
    expect($updateInfo['release_notes'])->toBe($releaseNotes);
});
