<?php

/**
 * API Usage Examples for Filament App Version Manager
 * 
 * This file contains practical examples of how to use the API endpoints
 * in various scenarios and programming languages.
 */

// =============================================================================
// PHP Examples using Guzzle HTTP Client
// =============================================================================

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AppVersionChecker
{
    private Client $client;
    private string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->client = new Client();
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Check for app updates
     */
    public function checkForUpdates(string $platform, string $currentVersion, ?string $locale = 'en'): array
    {
        try {
            $response = $this->client->post($this->baseUrl . '/api/version/check', [
                'json' => [
                    'platform' => $platform,
                    'current_version' => $currentVersion,
                    'locale' => $locale,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 10,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Network error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get version statistics
     */
    public function getVersionStats(): array
    {
        try {
            $response = $this->client->get($this->baseUrl . '/api/version/stats', [
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 10,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Network error: ' . $e->getMessage(),
            ];
        }
    }
}

// Usage example
$checker = new AppVersionChecker('https://yourapp.com');

// Check for iOS updates
$result = $checker->checkForUpdates('ios', '1.0.0', 'en');
if ($result['success'] && $result['update_available']) {
    echo "Update available: {$result['latest_version']}\n";
    echo "Download URL: {$result['download_url']}\n";
    echo "Force update: " . ($result['force_update'] ? 'Yes' : 'No') . "\n";

    // Handle release notes based on response format
    if (is_string($result['release_notes'])) {
        // Localized response (when locale was specified)
        echo "Release notes: {$result['release_notes']}\n";
    } elseif (is_array($result['release_notes']) && isset($result['release_notes']['en'])) {
        // All locales response (when no locale was specified)
        echo "Release notes (EN): {$result['release_notes']['en']}\n";
    }
}

// =============================================================================
// JavaScript/TypeScript Examples
// =============================================================================

/*
// Using fetch API
class AppVersionManager {
    constructor(baseUrl) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
    }

    async checkForUpdates(platform, currentVersion, locale = 'en') {
        try {
            const response = await fetch(`${this.baseUrl}/api/version/check`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    platform,
                    current_version: currentVersion,
                    locale,
                }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            return {
                success: false,
                error: `Network error: ${error.message}`,
            };
        }
    }

    async getVersionStats() {
        try {
            const response = await fetch(`${this.baseUrl}/api/version/stats`, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            return {
                success: false,
                error: `Network error: ${error.message}`,
            };
        }
    }
}

// Usage
const versionManager = new AppVersionManager('https://yourapp.com');

// Check for updates
versionManager.checkForUpdates('android', '2.0.0', 'ar')
    .then(result => {
        if (result.success && result.update_available) {
            console.log('Update available:', result.latest_version);
            console.log('Force update:', result.force_update);

            // Handle release notes based on response format
            if (typeof result.release_notes === 'string') {
                // Localized response (when locale was specified)
                console.log('Release notes (Arabic):', result.release_notes);
            } else if (typeof result.release_notes === 'object' && result.release_notes.ar) {
                // All locales response (when no locale was specified)
                console.log('Release notes (Arabic):', result.release_notes.ar);
            }

            // Show update dialog to user
            showUpdateDialog(result);
        }
    })
    .catch(error => {
        console.error('Failed to check for updates:', error);
    });

// Using axios (if you prefer)
import axios from 'axios';

const api = axios.create({
    baseURL: 'https://yourapp.com/api/version',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Check for updates with axios
async function checkUpdates(platform, version) {
    try {
        const response = await api.post('/check', {
            platform,
            current_version: version,
            locale: navigator.language.split('-')[0], // Use browser language
        });

        return response.data;
    } catch (error) {
        if (error.response) {
            // Server responded with error status
            return {
                success: false,
                error: error.response.data.message || 'Server error',
                code: error.response.status,
            };
        } else if (error.request) {
            // Network error
            return {
                success: false,
                error: 'Network error - please check your connection',
            };
        } else {
            return {
                success: false,
                error: error.message,
            };
        }
    }
}
*/

// =============================================================================
// Swift (iOS) Example
// =============================================================================

/*
import Foundation

class AppVersionManager {
    private let baseURL: String
    
    init(baseURL: String) {
        self.baseURL = baseURL.trimmingCharacters(in: CharacterSet(charactersIn: "/"))
    }
    
    func checkForUpdates(platform: String, currentVersion: String, locale: String = "en", completion: @escaping (Result<[String: Any], Error>) -> Void) {
        guard let url = URL(string: "\(baseURL)/api/version/check") else {
            completion(.failure(NSError(domain: "InvalidURL", code: 0, userInfo: nil)))
            return
        }
        
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        
        let body = [
            "platform": platform,
            "current_version": currentVersion,
            "locale": locale
        ]
        
        do {
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
        } catch {
            completion(.failure(error))
            return
        }
        
        URLSession.shared.dataTask(with: request) { data, response, error in
            if let error = error {
                completion(.failure(error))
                return
            }
            
            guard let data = data else {
                completion(.failure(NSError(domain: "NoData", code: 0, userInfo: nil)))
                return
            }
            
            do {
                if let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] {
                    completion(.success(json))
                } else {
                    completion(.failure(NSError(domain: "InvalidJSON", code: 0, userInfo: nil)))
                }
            } catch {
                completion(.failure(error))
            }
        }.resume()
    }
}

// Usage
let versionManager = AppVersionManager(baseURL: "https://yourapp.com")

versionManager.checkForUpdates(platform: "ios", currentVersion: "1.0.0") { result in
    switch result {
    case .success(let data):
        if let success = data["success"] as? Bool, success,
           let updateAvailable = data["update_available"] as? Bool, updateAvailable {
            
            let latestVersion = data["latest_version"] as? String ?? ""
            let forceUpdate = data["force_update"] as? Bool ?? false
            let downloadURL = data["download_url"] as? String ?? ""
            
            DispatchQueue.main.async {
                // Show update alert to user
                self.showUpdateAlert(version: latestVersion, forceUpdate: forceUpdate, downloadURL: downloadURL)
            }
        }
    case .failure(let error):
        print("Failed to check for updates: \(error)")
    }
}
*/

// =============================================================================
// Android (Kotlin) Example
// =============================================================================

/*
import kotlinx.coroutines.*
import kotlinx.serialization.*
import kotlinx.serialization.json.*
import java.net.HttpURLConnection
import java.net.URL

@Serializable
data class VersionCheckRequest(
    val platform: String,
    val current_version: String,
    val locale: String = "en"
)

@Serializable
data class VersionCheckResponse(
    val success: Boolean,
    val update_available: Boolean? = null,
    val latest_version: String? = null,
    val force_update: Boolean? = null,
    val download_url: String? = null,
    val release_notes: Map<String, String>? = null,
    val error: String? = null
)

class AppVersionManager(private val baseUrl: String) {
    private val json = Json { ignoreUnknownKeys = true }
    
    suspend fun checkForUpdates(platform: String, currentVersion: String, locale: String = "en"): VersionCheckResponse {
        return withContext(Dispatchers.IO) {
            try {
                val url = URL("${baseUrl.trimEnd('/')}/api/version/check")
                val connection = url.openConnection() as HttpURLConnection
                
                connection.requestMethod = "POST"
                connection.setRequestProperty("Content-Type", "application/json")
                connection.setRequestProperty("Accept", "application/json")
                connection.doOutput = true
                
                val request = VersionCheckRequest(platform, currentVersion, locale)
                val requestBody = json.encodeToString(request)
                
                connection.outputStream.use { os ->
                    os.write(requestBody.toByteArray())
                }
                
                val responseCode = connection.responseCode
                val responseBody = if (responseCode == 200) {
                    connection.inputStream.bufferedReader().readText()
                } else {
                    connection.errorStream?.bufferedReader()?.readText() ?: ""
                }
                
                json.decodeFromString<VersionCheckResponse>(responseBody)
            } catch (e: Exception) {
                VersionCheckResponse(success = false, error = "Network error: ${e.message}")
            }
        }
    }
}

// Usage in Activity or Fragment
class MainActivity : AppCompatActivity() {
    private val versionManager = AppVersionManager("https://yourapp.com")
    
    private fun checkForUpdates() {
        lifecycleScope.launch {
            val result = versionManager.checkForUpdates("android", "1.0.0", "ar")
            
            if (result.success && result.update_available == true) {
                showUpdateDialog(
                    latestVersion = result.latest_version ?: "",
                    forceUpdate = result.force_update ?: false,
                    downloadUrl = result.download_url ?: "",
                    releaseNotes = when (result.release_notes) {
                        is String -> result.release_notes // Localized response
                        is Map<*, *> -> result.release_notes["ar"] as? String ?: "" // All locales response
                        else -> ""
                    }
                )
            }
        }
    }
}
*/
