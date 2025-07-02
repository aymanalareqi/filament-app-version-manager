<?php

namespace Alareqi\FilamentAppVersionManager\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\Enums\Platform;
use Alareqi\FilamentAppVersionManager\Facades\FilamentAppVersionManager;

class VersionController extends Controller
{
    /**
     * Check for app updates.
     */
    public function check(Request $request): JsonResponse
    {
        // Check if API is enabled
        if (!FilamentAppVersionManager::isApiEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Version API is disabled',
            ], 503);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'current_version' => [
                'required',
                'string',
                'max:' . config('filament-app-version-manager.validation.max_version_length', 20),
            ],
            'platform' => [
                'required',
                'string',
                'in:' . implode(',', Platform::values()),
            ],
            'build_number' => [
                'nullable',
                'string',
                'max:' . config('filament-app-version-manager.validation.max_build_number_length', 50),
            ],
            'locale' => [
                'nullable',
                'string',
                'max:10',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $currentVersion = $request->input('current_version');
        $platform = Platform::from($request->input('platform'));
        $buildNumber = $request->input('build_number');
        $locale = $request->input('locale');

        // Create cache key (include locale for proper caching)
        $cacheKey = "app_version_check_{$platform->value}_{$currentVersion}_{$buildNumber}_{$locale}";
        $cacheTtl = FilamentAppVersionManager::getCacheTtl();

        // Try to get from cache
        $response = Cache::remember($cacheKey, $cacheTtl, function () use ($currentVersion, $platform, $locale) {
            return $this->performVersionCheck($currentVersion, $platform, $locale);
        });

        return response()->json($response);
    }

    /**
     * Perform the actual version check.
     */
    private function performVersionCheck(string $currentVersion, Platform $platform, ?string $locale = null): array
    {
        try {
            // Validate semantic versioning if enabled
            if (config('filament-app-version-manager.validation.semantic_versioning', true)) {
                if (!AppVersion::validateSemanticVersion($currentVersion)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid version format. Please use semantic versioning (e.g., 1.0.0)',
                    ];
                }
            }

            // Get update information
            $updateInfo = AppVersion::isUpdateAvailable($currentVersion, $platform, $locale);

            // Prepare response
            $response = [
                'success' => true,
                'current_version' => $currentVersion,
                'platform' => $platform->value,
                'platform_label' => $platform->getLabel(),
                'update_available' => $updateInfo['update_available'],
                'latest_version' => $updateInfo['latest_version'],
                'force_update' => $updateInfo['force_update'],
                'download_url' => $updateInfo['download_url'],
                'release_date' => $updateInfo['release_date'],
                'release_notes' => $updateInfo['release_notes'],
                'checked_at' => now()->toISOString(),
            ];

            // Add additional metadata if available
            if ($updateInfo['update_available'] && $updateInfo['latest_version']) {
                $latestVersionRecord = AppVersion::getLatestForPlatform($platform);
                if ($latestVersionRecord && $latestVersionRecord->metadata) {
                    $response['metadata'] = $latestVersionRecord->metadata;
                }
            }

            return $response;
        } catch (\Exception $e) {
            // Log the error
            logger()->error('Version check failed', [
                'current_version' => $currentVersion,
                'platform' => $platform->value,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while checking for updates',
                'error_code' => 'VERSION_CHECK_FAILED',
            ];
        }
    }

    /**
     * Get version check statistics (optional endpoint for analytics).
     */
    public function stats(): JsonResponse
    {
        // Check if API is enabled
        if (!FilamentAppVersionManager::isApiEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Version API is disabled',
            ], 503);
        }

        try {
            $stats = [
                'success' => true,
                'total_versions' => AppVersion::count(),
                'active_versions' => AppVersion::active()->count(),
                'beta_versions' => AppVersion::where('is_beta', true)->count(),
                'platforms' => [],
            ];

            // Get platform-specific stats
            foreach (Platform::cases() as $platform) {
                $stats['platforms'][$platform->value] = [
                    'label' => $platform->getLabel(),
                    'total_versions' => AppVersion::forPlatform($platform)->count(),
                    'active_versions' => AppVersion::forPlatform($platform)->active()->count(),
                    'latest_version' => AppVersion::getLatestForPlatform($platform)?->version,
                ];
            }

            return response()->json($stats);
        } catch (\Exception $e) {
            logger()->error('Version stats failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching version statistics',
                'error_code' => 'STATS_FAILED',
            ], 500);
        }
    }
}
