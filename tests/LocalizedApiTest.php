<?php

use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

beforeEach(function () {
    // Set up test configuration
    config([
        'filament-app-version-manager.api.enabled' => true,
        'filament-app-version-manager.api.prefix' => 'api/version',
        'filament-app-version-manager.api.middleware' => ['throttle:60,1'],
        'filament-app-version-manager.localization.default_locale' => 'en',
        'filament-app-version-manager.localization.fallback_locale' => 'en',
        'filament-app-version-manager.localization.supported_locales' => ['en', 'ar', 'fr', 'es'],
    ]);
});

it('returns localized release notes via API with locale parameter', function () {
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

    // Test English locale
    $response = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => 'en',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'update_available' => true,
            'release_notes' => 'English release notes',
        ]);

    // Test Arabic locale
    $response = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => 'ar',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'update_available' => true,
            'release_notes' => 'ملاحظات الإصدار بالعربية',
        ]);

    // Test French locale
    $response = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => 'fr',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'update_available' => true,
            'release_notes' => 'Notes de version en français',
        ]);
});

it('falls back to default locale when requested locale not available via API', function () {
    AppVersion::create([
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
    $response = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => 'es',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'update_available' => true,
            'release_notes' => 'English release notes',
        ]);
});

it('maintains backward compatibility when no locale provided via API', function () {
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
    $response = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'update_available' => true,
            'release_notes' => $releaseNotes,
        ]);
});

it('validates locale parameter format via API', function () {
    AppVersion::create([
        'version' => '1.0.0',
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => [
            'en' => 'English release notes',
        ],
        'is_active' => true,
    ]);

    // Test with invalid locale (too long)
    $response = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => 'this-is-too-long-locale',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['locale']);
});

it('handles null locale parameter via API', function () {
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

    // Test with explicit null locale
    $response = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => null,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'update_available' => true,
            'release_notes' => $releaseNotes,
        ]);
});

it('caches responses with locale-specific cache keys', function () {
    AppVersion::create([
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

    // First request with English locale
    $response1 = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => 'en',
    ]);

    $response1->assertStatus(200)
        ->assertJson([
            'release_notes' => 'English release notes',
        ]);

    // Second request with Arabic locale should return different content
    $response2 = $this->postJson('/api/version/check', [
        'current_version' => '0.9.0',
        'platform' => 'ios',
        'locale' => 'ar',
    ]);

    $response2->assertStatus(200)
        ->assertJson([
            'release_notes' => 'ملاحظات الإصدار بالعربية',
        ]);

    // Verify different responses for different locales
    expect($response1->json('release_notes'))->not->toBe($response2->json('release_notes'));
});

it('handles empty release notes gracefully via API', function () {
    // Clean up any existing versions and clear cache
    AppVersion::query()->delete();
    \Illuminate\Support\Facades\Cache::flush();

    AppVersion::create([
        'version' => '2.0.0', // Use a higher version to ensure it's the latest
        'platform' => Platform::IOS,
        'release_date' => now(),
        'download_url' => 'https://example.com',
        'release_notes' => null,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/version/check', [
        'current_version' => '1.9.0', // Use a lower version to ensure update is available
        'platform' => 'ios',
        'locale' => 'en',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'update_available' => true,
            'release_notes' => null,
        ]);
});
