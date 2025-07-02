<?php

namespace Alareqi\FilamentAppVersionManager\Database\Factories;

use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Alareqi\FilamentAppVersionManager\Models\AppVersion>
 */
class AppVersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = $this->faker->randomElement(Platform::cases());

        return [
            'version' => $this->faker->semver(),
            'build_number' => $this->faker->numberBetween(100, 9999),
            'platform' => $platform,
            'minimum_required_version' => null,
            'release_notes' => [
                'en' => $this->faker->paragraph(),
                'ar' => 'ملاحظات الإصدار باللغة العربية',
            ],
            'release_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'download_url' => $this->generateDownloadUrl($platform),
            'force_update' => $this->faker->boolean(20), // 20% chance of force update
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_beta' => $this->faker->boolean(10), // 10% chance of being beta
            'is_rollback' => false,
            'metadata' => $this->generateMetadata($platform),
        ];
    }

    /**
     * Indicate that the version is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the version is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the version is a beta version.
     */
    public function beta(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_beta' => true,
        ]);
    }

    /**
     * Indicate that the version requires a force update.
     */
    public function forceUpdate(): static
    {
        return $this->state(fn(array $attributes) => [
            'force_update' => true,
        ]);
    }

    /**
     * Indicate that the version is a rollback version.
     */
    public function rollback(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_rollback' => true,
        ]);
    }

    /**
     * Set the platform for the version.
     */
    public function platform(Platform $platform): static
    {
        return $this->state(fn(array $attributes) => [
            'platform' => $platform,
            'download_url' => $this->generateDownloadUrl($platform),
            'metadata' => $this->generateMetadata($platform),
        ]);
    }

    /**
     * Generate a download URL based on platform.
     */
    private function generateDownloadUrl(Platform $platform): string
    {
        return match ($platform) {
            Platform::IOS => 'https://apps.apple.com/app/salawati/id' . $this->faker->numberBetween(100000000, 999999999),
            Platform::ANDROID => 'https://play.google.com/store/apps/details?id=com.salawati.app',
            Platform::ALL => $this->faker->url(),
        };
    }

    /**
     * Generate metadata based on platform.
     */
    private function generateMetadata(Platform $platform): array
    {
        $baseMetadata = [
            'app_size' => $this->faker->randomFloat(1, 30, 100) . ' MB',
            'features' => $this->faker->randomElements([
                'Prayer tracking',
                'Qibla direction',
                'Prayer times',
                'Quran reading',
                'Dhikr counter',
                'Islamic calendar',
                'Notifications',
                'Dark mode',
            ], $this->faker->numberBetween(3, 6)),
        ];

        return match ($platform) {
            Platform::IOS => array_merge($baseMetadata, [
                'ios_version_required' => $this->faker->randomElement(['13.0', '14.0', '15.0', '16.0']),
            ]),
            Platform::ANDROID => array_merge($baseMetadata, [
                'android_version_required' => $this->faker->randomElement(['21', '23', '26', '28', '30']),
            ]),
            Platform::ALL => $baseMetadata,
        };
    }
}
