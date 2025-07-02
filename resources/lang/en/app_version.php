<?php

return [
    'singular_label' => 'App Version',
    'plural_label' => 'App Versions',
    'navigation_label' => 'App Versions',
    'navigation_group' => 'Version Management',

    'sections' => [
        'version_info' => 'Version Information',
        'release_info' => 'Release Information',
        'settings' => 'Settings',
        'metadata' => 'Additional Metadata',
    ],

    'fields' => [
        'version' => 'Version',
        'build_number' => 'Build Number',
        'platform' => 'Platform',
        'minimum_required_version' => 'Minimum Required Version',
        'release_date' => 'Release Date',
        'download_url' => 'Download URL',
        'release_notes' => 'Release Notes',
        'language' => 'Language',
        'notes' => 'Notes',
        'is_active' => 'Active',
        'force_update' => 'Force Update',
        'is_beta' => 'Beta Version',
        'is_rollback' => 'Rollback Version',
        'metadata' => 'Metadata',
        'key' => 'Key',
        'value' => 'Value',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'created_by' => 'Created By',
        'updated_by' => 'Updated By',
    ],

    'columns' => [
        'version' => 'Version',
        'platform' => 'Platform',
        'release_date' => 'Release Date',
        'is_active' => 'Active',
        'force_update' => 'Force Update',
        'is_beta' => 'Beta',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'placeholders' => [
        'version' => 'e.g., 1.0.0',
        'build_number' => 'e.g., 100',
        'minimum_required_version' => 'e.g., 0.9.0',
        'download_url' => 'https://example.com/download',
        'release_notes' => 'Enter release notes in :language...',
    ],

    'help' => [
        'version' => 'Use semantic versioning format (e.g., 1.0.0)',
        'build_number' => 'Internal build number for tracking',
        'platform' => 'Target platform for this version',
        'minimum_required_version' => 'Minimum version required to update to this version',
        'release_date' => 'Date when this version was/will be released',
        'download_url' => 'URL where users can download this version',
        'release_notes' => 'Add release notes in multiple languages',
        'is_active' => 'Whether this version is currently active and available',
        'force_update' => 'Whether users must update to this version',
        'is_beta' => 'Mark this as a beta/testing version',
        'is_rollback' => 'Mark this as a rollback version',
        'metadata' => 'Additional metadata in key-value format',
    ],

    "platforms" => [
        'ios' => 'iOS',
        'android' => 'Android',
    ],

    'filters' => [
        'platform' => 'Platform',
        'is_active' => 'Active Status',
        'force_update' => 'Force Update',
        'is_beta' => 'Beta Version',
    ],

    'tabs' => [
        'all' => 'All',
    ],

    'actions' => [
        'create' => 'New Version',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'duplicate' => 'Duplicate',
        'create_rollback' => 'Create Rollback',
    ],

    'messages' => [
        'created' => 'App version created successfully',
        'updated' => 'App version updated successfully',
        'deleted' => 'App version deleted successfully',
        'duplicate_created' => 'Version duplicated successfully',
        'rollback_created' => 'Rollback version created successfully',
        'copied' => 'Version copied to clipboard',
    ],

    'confirmations' => [
        'delete' => 'Are you sure you want to delete this version?',
        'duplicate' => 'This will create a copy of the current version with an incremented version number.',
        'create_rollback' => 'This will create a rollback version based on the current version.',
    ],

    'notifications' => [
        'force_update_created' => [
            'title' => 'Force Update Created',
            'body' => 'Version :version for :platform has been created as a force update.',
        ],
        'beta_version_created' => [
            'title' => 'Beta Version Created',
            'body' => 'Beta version :version for :platform has been created.',
        ],
        'force_update_enabled' => [
            'title' => 'Force Update Enabled',
            'body' => 'Version :version for :platform is now marked as a force update.',
        ],
        'version_activated' => [
            'title' => 'Version Activated',
            'body' => 'Version :version for :platform has been activated.',
        ],
    ],

    'validation' => [
        'version_format' => 'Version must follow semantic versioning format (e.g., 1.0.0)',
        'minimum_version_format' => 'Minimum required version must follow semantic versioning format',
        'unique_version_platform' => 'This version already exists for the selected platform',
    ],

    'empty_state' => [
        'heading' => 'No app versions',
        'description' => 'Get started by creating your first app version.',
    ],
];
