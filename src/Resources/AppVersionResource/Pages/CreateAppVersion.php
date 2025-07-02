<?php

namespace Alareqi\FilamentAppVersionManager\Resources\AppVersionResource\Pages;

use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAppVersion extends CreateRecord
{
    protected static string $resource = AppVersionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('filament-app-version-manager::app_version.messages.created');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values from configuration
        $data['is_active'] = $data['is_active'] ?? config('filament-app-version-manager.defaults.is_active', true);
        $data['is_beta'] = $data['is_beta'] ?? config('filament-app-version-manager.defaults.is_beta', false);
        $data['is_rollback'] = $data['is_rollback'] ?? config('filament-app-version-manager.defaults.is_rollback', false);
        $data['force_update'] = $data['force_update'] ?? config('filament-app-version-manager.defaults.force_update', false);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send additional notification if this is a force update
        if ($this->record->force_update) {
            Notification::make()
                ->title(__('filament-app-version-manager::app_version.notifications.force_update_created.title'))
                ->body(__('filament-app-version-manager::app_version.notifications.force_update_created.body', [
                    'version' => $this->record->version,
                    'platform' => $this->record->platform->getLabel(),
                ]))
                ->warning()
                ->send();
        }

        // Send notification if this is a beta version
        if ($this->record->is_beta) {
            Notification::make()
                ->title(__('filament-app-version-manager::app_version.notifications.beta_version_created.title'))
                ->body(__('filament-app-version-manager::app_version.notifications.beta_version_created.body', [
                    'version' => $this->record->version,
                    'platform' => $this->record->platform->getLabel(),
                ]))
                ->info()
                ->send();
        }
    }
}
