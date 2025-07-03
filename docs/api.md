# API Documentation

The Filament App Version Manager provides RESTful API endpoints for version checking and management. All endpoints support caching, rate limiting, and localization.

## Configuration

### Enable API Routes

API routes are enabled by default but can be configured:

```php
// config/filament-app-version-manager.php
'api' => [
    'enabled' => true,                    // Enable/disable API endpoints
    'prefix' => 'api/version',           // API route prefix
    'middleware' => ['throttle:60,1'],   // Rate limiting middleware
    'cache_ttl' => 300,                  // Cache TTL in seconds (5 minutes)
    'enable_stats' => false,             // Enable statistics endpoint
],
```

### Plugin Configuration

You can also configure API settings using the fluent API:

```php
FilamentAppVersionManagerPlugin::make()
    ->enableApiRoutes(true)
    ->apiPrefix('api/v1/version')
    ->apiCacheTtl(600)
    ->apiMiddleware(['throttle:100,1', 'auth:api']);
```

## Endpoints

### Version Check

**POST** `/api/version/check`

Check for available updates for a specific platform and version.

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `platform` | string | Yes | Platform identifier: `"ios"` or `"android"` |
| `current_version` | string | Yes | Current app version (semantic versioning) |
| `build_number` | string | No | Build number for additional version tracking |
| `locale` | string | No | Preferred locale for localized content (e.g., `"en"`, `"ar"`, `"fr"`) |

#### Request Example

```http
POST /api/version/check
Content-Type: application/json

{
    "platform": "ios",
    "current_version": "1.0.0",
    "build_number": "100",
    "locale": "en"
}
```

#### Response Format

**Success Response (Update Available - No Locale Specified):**
```json
{
    "success": true,
    "current_version": "1.0.0",
    "platform": "ios",
    "platform_label": "iOS",
    "update_available": true,
    "latest_version": "1.1.0",
    "force_update": false,
    "is_beta": false,
    "download_url": "https://apps.apple.com/app/yourapp",
    "release_date": "2025-07-01T10:00:00.000000Z",
    "release_notes": {
        "en": "Bug fixes and improvements",
        "ar": "إصلاح الأخطاء والتحسينات"
    },
    "metadata": {
        "app_size": "45.2 MB",
        "features": ["New UI", "Performance improvements"]
    },
    "checked_at": "2025-07-01T12:00:00.000000Z"
}
```

**Success Response (No Update Available):**
```json
{
    "success": true,
    "current_version": "1.1.0",
    "platform": "ios",
    "platform_label": "iOS",
    "update_available": false,
    "latest_version": "1.1.0",
    "message": "You are using the latest version",
    "checked_at": "2025-07-01T12:00:00.000000Z"
}
```

## Localization

### Locale Parameter

The API supports localization through the optional `locale` parameter. When provided, the API returns localized content for the specified language.

#### Localized Request

```http
POST /api/version/check
Content-Type: application/json

{
    "platform": "ios",
    "current_version": "1.0.0",
    "locale": "ar"
}
```

#### Localized Response

**Success Response (Update Available - With Locale Specified):**

When a locale is specified, the `release_notes` field returns a single localized string instead of an object containing all translations:

```json
{
    "success": true,
    "current_version": "1.0.0",
    "platform": "ios",
    "platform_label": "iOS",
    "update_available": true,
    "latest_version": "1.1.0",
    "force_update": false,
    "download_url": "https://apps.apple.com/app/yourapp",
    "release_date": "2025-07-01T10:00:00.000000Z",
    "release_notes": "إصلاح الأخطاء والتحسينات",
    "checked_at": "2025-07-01T12:00:00.000000Z"
}
```

**Key Difference:**
- **Without locale**: `release_notes` is an object with all available translations
- **With locale**: `release_notes` is a string with the localized content for the requested locale

## Release Notes Response Format

The format of the `release_notes` field in API responses depends on whether a locale parameter is included in the request:

### Without Locale Parameter

When no `locale` parameter is provided, the API returns all available translations as an object:

```bash
curl -X POST https://yourapp.com/api/version/check \
  -H "Content-Type: application/json" \
  -d '{
    "platform": "ios",
    "current_version": "1.0.0"
  }'
```

Response:
```json
{
  "success": true,
  "release_notes": {
    "en": "Bug fixes and improvements",
    "ar": "إصلاح الأخطاء والتحسينات",
    "fr": "Corrections de bugs et améliorations"
  }
}
```

### With Locale Parameter

When a `locale` parameter is provided, the API returns only the localized string for that specific locale:

```bash
curl -X POST https://yourapp.com/api/version/check \
  -H "Content-Type: application/json" \
  -d '{
    "platform": "ios",
    "current_version": "1.0.0",
    "locale": "ar"
  }'
```

Response:
```json
{
  "success": true,
  "release_notes": "إصلاح الأخطاء والتحسينات"
}
```

### Handling Both Formats in Code

When consuming the API, you should handle both response formats:

**PHP Example:**
```php
if (is_string($response['release_notes'])) {
    // Localized response
    $notes = $response['release_notes'];
} elseif (is_array($response['release_notes'])) {
    // All locales response - get specific locale or default
    $notes = $response['release_notes']['en'] ?? reset($response['release_notes']);
}
```

**JavaScript Example:**
```javascript
let releaseNotes;
if (typeof response.release_notes === 'string') {
    // Localized response
    releaseNotes = response.release_notes;
} else if (typeof response.release_notes === 'object') {
    // All locales response - get specific locale or default
    releaseNotes = response.release_notes.en || Object.values(response.release_notes)[0];
}
```

### Fallback Logic

The API implements intelligent fallback logic for localization:

1. **Requested Locale**: Returns content in the requested locale if available
2. **Default Locale**: Falls back to the configured default locale
3. **Fallback Locale**: Falls back to the configured fallback locale
4. **First Available**: Returns the first available translation
5. **Backward Compatibility**: Returns all translations when no locale is specified

#### Configuration

Configure localization settings in your config file:

```php
// config/filament-app-version-manager.php
'localization' => [
    'default_locale' => 'en',
    'fallback_locale' => 'en',
    'supported_locales' => ['en', 'ar', 'fr', 'es', 'de'],
],
```

### Supported Locales

The API accepts any valid locale code. Common examples:

- `en` - English
- `ar` - Arabic
- `fr` - French
- `es` - Spanish
- `de` - German
- `zh` - Chinese
- `ja` - Japanese
- `ko` - Korean

## Error Handling

### Validation Errors

**HTTP 422 - Unprocessable Entity**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "platform": ["The platform field is required."],
        "current_version": ["The current version field is required."],
        "locale": ["The locale must not be greater than 10 characters."]
    }
}
```

### Server Errors

**HTTP 500 - Internal Server Error**

```json
{
    "success": false,
    "message": "An error occurred while checking for updates.",
    "error": "Database connection failed"
}
```

## Caching

### Cache Behavior

- API responses are cached based on platform, version, build number, and locale
- Cache keys include all request parameters to prevent cross-contamination
- Default cache TTL is 5 minutes (300 seconds)
- Cache can be configured per environment

### Cache Keys

Cache keys follow this pattern:
```
app_version_check_{platform}_{current_version}_{build_number}_{locale}
```

Examples:
- `app_version_check_ios_1.0.0__en`
- `app_version_check_android_2.1.0_150_ar`

### Cache Configuration

```php
FilamentAppVersionManagerPlugin::make()
    ->apiCacheTtl(600); // 10 minutes

// Or in config file
'api' => [
    'cache_ttl' => 600, // 10 minutes
],
```

## Rate Limiting

### Default Limits

- 60 requests per minute per IP address
- Configurable through middleware

### Custom Rate Limiting

```php
FilamentAppVersionManagerPlugin::make()
    ->apiMiddleware(['throttle:100,1']); // 100 requests per minute

// Or in config file
'api' => [
    'middleware' => ['throttle:100,1'],
],
```

## Integration Examples

### iOS Swift

```swift
struct VersionCheckRequest: Codable {
    let platform: String
    let currentVersion: String
    let locale: String?
}

struct VersionCheckResponse: Codable {
    let success: Bool
    let updateAvailable: Bool
    let latestVersion: String?
    let forceUpdate: Bool
    let downloadUrl: String?
    let releaseNotes: String?
}

func checkForUpdates() async {
    let request = VersionCheckRequest(
        platform: "ios",
        currentVersion: Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "1.0.0",
        locale: Locale.current.languageCode
    )
    
    // Make API request...
}
```

### Android Kotlin

```kotlin
data class VersionCheckRequest(
    val platform: String,
    val currentVersion: String,
    val locale: String?
)

data class VersionCheckResponse(
    val success: Boolean,
    val updateAvailable: Boolean,
    val latestVersion: String?,
    val forceUpdate: Boolean,
    val downloadUrl: String?,
    val releaseNotes: String?
)

suspend fun checkForUpdates(): VersionCheckResponse {
    val request = VersionCheckRequest(
        platform = "android",
        currentVersion = BuildConfig.VERSION_NAME,
        locale = Locale.getDefault().language
    )
    
    // Make API request...
}
```

### JavaScript/React Native

```javascript
const checkForUpdates = async () => {
  const response = await fetch('/api/version/check', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      platform: Platform.OS,
      current_version: DeviceInfo.getVersion(),
      locale: I18n.locale,
    }),
  });
  
  const data = await response.json();
  return data;
};
```

## Security Considerations

### Authentication

While the version check endpoint is typically public, you can add authentication:

```php
FilamentAppVersionManagerPlugin::make()
    ->apiMiddleware(['throttle:60,1', 'auth:sanctum']);
```

### CORS

Configure CORS for cross-origin requests:

```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
```

### Input Validation

All inputs are validated:
- Platform must be a valid enum value
- Version must follow semantic versioning
- Locale must be a valid locale code (max 10 characters)
- Build number is optional but validated if provided

## Monitoring and Analytics

### Request Logging

Enable request logging for monitoring:

```php
// In your middleware or service provider
Log::info('Version check request', [
    'platform' => $request->platform,
    'version' => $request->current_version,
    'locale' => $request->locale,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

### Metrics Collection

Track API usage metrics:

```php
// Custom middleware for metrics
class VersionCheckMetrics
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Track metrics
        Metrics::increment('version_check.requests', [
            'platform' => $request->platform,
            'locale' => $request->locale ?? 'none',
        ]);
        
        return $response;
    }
}
```
