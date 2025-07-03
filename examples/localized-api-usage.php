<?php

/**
 * Localized API Usage Examples
 * 
 * This file demonstrates how to use the localized API endpoints
 * for version checking with different locales.
 */

// Example 1: Basic API request with locale
function checkVersionWithLocale($platform, $currentVersion, $locale = null)
{
    $url = 'https://yourapp.com/api/version/check';

    $data = [
        'platform' => $platform,
        'current_version' => $currentVersion,
    ];

    // Add locale if provided
    if ($locale) {
        $data['locale'] = $locale;
    }

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return json_decode($result, true);
}

// Example 2: Check version with English locale
$englishResponse = checkVersionWithLocale('ios', '1.0.0', 'en');
echo "English Response:\n";
print_r($englishResponse);

// Example 3: Check version with Arabic locale
$arabicResponse = checkVersionWithLocale('ios', '1.0.0', 'ar');
echo "\nArabic Response:\n";
print_r($arabicResponse);

// Example 4: Check version without locale (backward compatibility)
$defaultResponse = checkVersionWithLocale('ios', '1.0.0');
echo "\nDefault Response (all locales):\n";
print_r($defaultResponse);

// Example 5: Using cURL for more control
function checkVersionWithCurl($platform, $currentVersion, $locale = null)
{
    $url = 'https://yourapp.com/api/version/check';

    $data = [
        'platform' => $platform,
        'current_version' => $currentVersion,
    ];

    if ($locale) {
        $data['locale'] = $locale;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("API request failed with HTTP code: $httpCode");
    }

    return json_decode($response, true);
}

// Example 6: Multi-locale version checking
function checkMultipleLocales($platform, $currentVersion, $locales)
{
    $results = [];

    foreach ($locales as $locale) {
        try {
            $results[$locale] = checkVersionWithCurl($platform, $currentVersion, $locale);
            echo "✓ Successfully checked version for locale: $locale\n";
        } catch (Exception $e) {
            echo "✗ Failed to check version for locale $locale: " . $e->getMessage() . "\n";
            $results[$locale] = null;
        }
    }

    return $results;
}

// Example usage of multi-locale checking
$locales = ['en', 'ar', 'fr', 'es'];
$multiLocaleResults = checkMultipleLocales('android', '2.1.0', $locales);

// Example 7: Handling API responses
function handleVersionCheckResponse($response, $locale = null)
{
    if (!$response || !$response['success']) {
        echo "❌ Version check failed\n";
        return false;
    }

    if ($response['update_available']) {
        echo "🔄 Update available!\n";
        echo "Current: {$response['current_version']}\n";
        echo "Latest: {$response['latest_version']}\n";

        if ($response['force_update']) {
            echo "⚠️  This is a mandatory update\n";
        }

        // Handle localized release notes
        if (isset($response['release_notes'])) {
            if (is_string($response['release_notes'])) {
                // Localized response - when locale was specified in request
                echo "📝 Release Notes ($locale): {$response['release_notes']}\n";
            } elseif (is_array($response['release_notes'])) {
                // All locales response - when no locale was specified in request
                echo "📝 Release Notes (all locales):\n";
                foreach ($response['release_notes'] as $lang => $notes) {
                    echo "  - $lang: $notes\n";
                }
            }
        }

        if (isset($response['download_url'])) {
            echo "📱 Download: {$response['download_url']}\n";
        }

        return true;
    } else {
        echo "✅ You're using the latest version\n";
        return false;
    }
}

// Example 8: Complete version check workflow
function completeVersionCheck($platform, $currentVersion, $userLocale = 'en')
{
    echo "🔍 Checking for updates...\n";
    echo "Platform: $platform\n";
    echo "Current Version: $currentVersion\n";
    echo "User Locale: $userLocale\n\n";

    try {
        // First, try with user's preferred locale
        $response = checkVersionWithCurl($platform, $currentVersion, $userLocale);
        $updateAvailable = handleVersionCheckResponse($response, $userLocale);

        // If update is available and it's a force update, handle accordingly
        if ($updateAvailable && $response['force_update']) {
            echo "\n🚨 CRITICAL UPDATE REQUIRED\n";
            echo "This update is mandatory and must be installed.\n";

            // In a real app, you would redirect to app store or show update dialog
            return 'force_update_required';
        } elseif ($updateAvailable) {
            echo "\n💡 Optional update available\n";
            echo "Users can choose to update now or later.\n";

            return 'optional_update_available';
        } else {
            return 'up_to_date';
        }
    } catch (Exception $e) {
        echo "❌ Error checking for updates: " . $e->getMessage() . "\n";

        // Fallback: try without locale
        try {
            echo "🔄 Retrying without locale...\n";
            $response = checkVersionWithCurl($platform, $currentVersion);
            return handleVersionCheckResponse($response) ? 'update_available' : 'up_to_date';
        } catch (Exception $fallbackError) {
            echo "❌ Fallback also failed: " . $fallbackError->getMessage() . "\n";
            return 'check_failed';
        }
    }
}

// Example usage
echo "=== Localized API Usage Examples ===\n\n";

// Test with different scenarios
$testCases = [
    ['ios', '1.0.0', 'en'],
    ['android', '2.1.0', 'ar'],
    ['ios', '3.0.0', 'fr'],
];

foreach ($testCases as $index => $testCase) {
    echo "--- Test Case " . ($index + 1) . " ---\n";
    $result = completeVersionCheck($testCase[0], $testCase[1], $testCase[2]);
    echo "Result: $result\n\n";
}

// Example 9: Configuration for different environments
class VersionChecker
{
    private $baseUrl;
    private $timeout;
    private $defaultLocale;

    public function __construct($environment = 'production')
    {
        switch ($environment) {
            case 'development':
                $this->baseUrl = 'http://localhost:8000/api/version';
                $this->timeout = 10;
                break;
            case 'staging':
                $this->baseUrl = 'https://staging.yourapp.com/api/version';
                $this->timeout = 20;
                break;
            case 'production':
            default:
                $this->baseUrl = 'https://api.yourapp.com/api/version';
                $this->timeout = 30;
                break;
        }

        $this->defaultLocale = 'en';
    }

    public function checkVersion($platform, $currentVersion, $locale = null, $buildNumber = null)
    {
        $data = [
            'platform' => $platform,
            'current_version' => $currentVersion,
            'locale' => $locale ?: $this->defaultLocale,
        ];

        if ($buildNumber) {
            $data['build_number'] = $buildNumber;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/check');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: YourApp/1.0',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL error: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("HTTP error: $httpCode");
        }

        return json_decode($response, true);
    }
}

// Example usage of the VersionChecker class
echo "=== VersionChecker Class Example ===\n";

$checker = new VersionChecker('production');
try {
    $result = $checker->checkVersion('ios', '1.0.0', 'ar', '100');
    echo "✅ Version check successful\n";
    print_r($result);
} catch (Exception $e) {
    echo "❌ Version check failed: " . $e->getMessage() . "\n";
}
