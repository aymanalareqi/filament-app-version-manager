<?php

namespace Alareqi\FilamentAppVersionManager\Resources\AppVersionResource\Pages;

use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

class ListAppVersions extends ListRecords
{
    protected static string $resource = AppVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make(__('filament-app-version-manager::app_version.tabs.all')),
        ];

        // Add platform-specific tabs
        foreach (Platform::cases() as $platform) {
            $tabs[strtolower($platform->value)] = Tab::make($platform->getLabel())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('platform', $platform->value));
        }

        return $tabs;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add widgets here if needed
        ];
    }
}
