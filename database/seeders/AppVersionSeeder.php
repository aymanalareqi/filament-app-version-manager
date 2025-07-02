<?php

namespace Alareqi\FilamentAppVersionManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        AppVersion::truncate();

        // Create sample versions for iOS
        $this->createVersionsForPlatform(Platform::IOS);

        // Create sample versions for Android
        $this->createVersionsForPlatform(Platform::ANDROID);

        // Create some cross-platform versions
        $this->createCrossPlatformVersions();
    }

    /**
     * Create sample versions for a specific platform.
     */
    private function createVersionsForPlatform(Platform $platform): void
    {
        $versions = [
            [
                'version' => '1.0.0',
                'build_number' => '100',
                'release_date' => now()->subMonths(6),
                'is_active' => false,
                'release_notes' => [
                    'en' => 'Initial release with basic prayer tracking features.',
                    'ar' => 'الإصدار الأولي مع ميزات تتبع الصلاة الأساسية.',
                ],
            ],
            [
                'version' => '1.1.0',
                'build_number' => '110',
                'release_date' => now()->subMonths(5),
                'is_active' => false,
                'release_notes' => [
                    'en' => 'Added Qibla direction feature and improved UI.',
                    'ar' => 'تمت إضافة ميزة اتجاه القبلة وتحسين واجهة المستخدم.',
                ],
            ],
            [
                'version' => '1.2.0',
                'build_number' => '120',
                'release_date' => now()->subMonths(4),
                'is_active' => false,
                'release_notes' => [
                    'en' => 'Prayer times calculation improvements and bug fixes.',
                    'ar' => 'تحسينات في حساب أوقات الصلاة وإصلاح الأخطاء.',
                ],
            ],
            [
                'version' => '2.0.0',
                'build_number' => '200',
                'release_date' => now()->subMonths(3),
                'is_active' => false,
                'force_update' => true,
                'release_notes' => [
                    'en' => 'Major update with Quran reading feature and dark mode.',
                    'ar' => 'تحديث كبير مع ميزة قراءة القرآن والوضع المظلم.',
                ],
            ],
            [
                'version' => '2.1.0',
                'build_number' => '210',
                'release_date' => now()->subMonths(2),
                'is_active' => false,
                'release_notes' => [
                    'en' => 'Added Dhikr counter and Islamic calendar.',
                    'ar' => 'تمت إضافة عداد الأذكار والتقويم الإسلامي.',
                ],
            ],
            [
                'version' => '2.2.0',
                'build_number' => '220',
                'release_date' => now()->subMonth(),
                'is_active' => true,
                'release_notes' => [
                    'en' => 'Performance improvements and notification enhancements.',
                    'ar' => 'تحسينات في الأداء وتعزيزات الإشعارات.',
                ],
            ],
            [
                'version' => '2.3.0-beta',
                'build_number' => '230',
                'release_date' => now()->subWeeks(2),
                'is_active' => true,
                'is_beta' => true,
                'release_notes' => [
                    'en' => 'Beta version with new prayer reminder features.',
                    'ar' => 'نسخة تجريبية مع ميزات تذكير الصلاة الجديدة.',
                ],
            ],
        ];

        foreach ($versions as $versionData) {
            AppVersion::create(array_merge($versionData, [
                'platform' => $platform,
                'download_url' => $this->generateDownloadUrl($platform),
                'metadata' => $this->generateMetadata($platform),
            ]));
        }
    }

    /**
     * Create cross-platform versions.
     */
    private function createCrossPlatformVersions(): void
    {
        $versions = [
            [
                'version' => '3.0.0',
                'build_number' => '300',
                'release_date' => now()->addWeek(),
                'is_active' => false,
                'force_update' => true,
                'release_notes' => [
                    'en' => 'Major cross-platform update with unified experience.',
                    'ar' => 'تحديث كبير متعدد المنصات مع تجربة موحدة.',
                ],
                'metadata' => [
                    'features' => [
                        'Cross-platform sync',
                        'Enhanced UI/UX',
                        'New prayer analytics',
                        'Community features',
                    ],
                    'breaking_changes' => true,
                    'migration_required' => true,
                ],
            ],
        ];

        foreach ($versions as $versionData) {
            AppVersion::create(array_merge($versionData, [
                'platform' => Platform::ALL,
                'download_url' => 'https://salawati.app/download',
            ]));
        }
    }

    /**
     * Generate download URL based on platform.
     */
    private function generateDownloadUrl(Platform $platform): string
    {
        return match ($platform) {
            Platform::IOS => 'https://apps.apple.com/app/salawati/id123456789',
            Platform::ANDROID => 'https://play.google.com/store/apps/details?id=com.salawati.app',
            Platform::ALL => 'https://salawati.app/download',
        };
    }

    /**
     * Generate metadata based on platform.
     */
    private function generateMetadata(Platform $platform): array
    {
        $baseMetadata = [
            'app_size' => '45.2 MB',
            'features' => [
                'Prayer tracking',
                'Qibla direction',
                'Prayer times',
                'Quran reading',
                'Dhikr counter',
                'Islamic calendar',
            ],
        ];

        return match ($platform) {
            Platform::IOS => array_merge($baseMetadata, [
                'ios_version_required' => '14.0',
                'app_store_category' => 'Lifestyle',
            ]),
            Platform::ANDROID => array_merge($baseMetadata, [
                'android_version_required' => '21',
                'play_store_category' => 'Lifestyle',
            ]),
            Platform::ALL => $baseMetadata,
        };
    }
}
