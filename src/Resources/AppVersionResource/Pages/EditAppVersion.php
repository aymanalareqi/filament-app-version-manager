<?php

namespace Alareqi\FilamentAppVersionManager\Resources\AppVersionResource\Pages;

use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAppVersion extends EditRecord
{
    protected static string $resource = AppVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('filament-app-version-manager::app_version.actions.delete')),

            Actions\Action::make('duplicate')
                ->label(__('filament-app-version-manager::app_version.actions.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $newVersion = $this->record->replicate();
                    $newVersion->version = $this->generateNextVersion($this->record->version);
                    $newVersion->is_active = false; // New duplicated versions should be inactive by default
                    $newVersion->save();

                    Notification::make()
                        ->title(__('filament-app-version-manager::app_version.messages.duplicate_created'))
                        ->success()
                        ->send();

                    return redirect($this->getResource()::getUrl('edit', ['record' => $newVersion]));
                })
                ->requiresConfirmation()
                ->modalHeading(__('filament-app-version-manager::app_version.actions.duplicate'))
                ->modalDescription(__('filament-app-version-manager::app_version.confirmations.duplicate'))
                ->modalSubmitActionLabel(__('filament-app-version-manager::app_version.actions.duplicate')),

            Actions\Action::make('create_rollback')
                ->label(__('filament-app-version-manager::app_version.actions.create_rollback'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->visible(config('filament-app-version-manager.features.version_rollback', true))
                ->action(function () {
                    $rollbackVersion = $this->record->replicate();
                    $rollbackVersion->version = $this->generateRollbackVersion($this->record->version);
                    $rollbackVersion->is_rollback = true;
                    $rollbackVersion->is_active = false; // Rollback versions should be inactive by default
                    $rollbackVersion->force_update = true; // Rollbacks are usually force updates
                    $rollbackVersion->save();

                    Notification::make()
                        ->title(__('filament-app-version-manager::app_version.messages.rollback_created'))
                        ->warning()
                        ->send();

                    return redirect($this->getResource()::getUrl('edit', ['record' => $rollbackVersion]));
                })
                ->requiresConfirmation()
                ->modalHeading(__('filament-app-version-manager::app_version.actions.create_rollback'))
                ->modalDescription(__('filament-app-version-manager::app_version.confirmations.create_rollback'))
                ->modalSubmitActionLabel(__('filament-app-version-manager::app_version.actions.create_rollback')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament-app-version-manager::app_version.messages.updated');
    }

    protected function afterSave(): void
    {
        // Send notification if force update status changed
        if ($this->record->wasChanged('force_update') && $this->record->force_update) {
            Notification::make()
                ->title(__('filament-app-version-manager::app_version.notifications.force_update_enabled.title'))
                ->body(__('filament-app-version-manager::app_version.notifications.force_update_enabled.body', [
                    'version' => $this->record->version,
                    'platform' => $this->record->platform->getLabel(),
                ]))
                ->warning()
                ->send();
        }

        // Send notification if version was activated
        if ($this->record->wasChanged('is_active') && $this->record->is_active) {
            Notification::make()
                ->title(__('filament-app-version-manager::app_version.notifications.version_activated.title'))
                ->body(__('filament-app-version-manager::app_version.notifications.version_activated.body', [
                    'version' => $this->record->version,
                    'platform' => $this->record->platform->getLabel(),
                ]))
                ->success()
                ->send();
        }
    }

    /**
     * Generate the next version number for duplication.
     */
    private function generateNextVersion(string $currentVersion): string
    {
        // Simple increment of patch version
        $parts = explode('.', $currentVersion);
        if (count($parts) >= 3) {
            $parts[2] = (int)$parts[2] + 1;
            return implode('.', $parts);
        }

        return $currentVersion . '.1';
    }

    /**
     * Generate a rollback version number.
     */
    private function generateRollbackVersion(string $currentVersion): string
    {
        // Add rollback suffix
        return $currentVersion . '-rollback';
    }
}
