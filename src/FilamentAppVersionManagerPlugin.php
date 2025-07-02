<?php

namespace Alareqi\FilamentAppVersionManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource;

class FilamentAppVersionManagerPlugin implements Plugin
{
    protected bool $hasApiRoutes = true;

    protected string $navigationGroup = 'Version Management';

    protected ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected ?int $navigationSort = null;

    public function getId(): string
    {
        return 'filament-app-version-manager';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                AppVersionResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function hasApiRoutes(bool $condition = true): static
    {
        $this->hasApiRoutes = $condition;

        return $this;
    }

    public function getHasApiRoutes(): bool
    {
        return $this->hasApiRoutes;
    }

    public function navigationGroup(string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): string
    {
        return $this->navigationGroup;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon;
    }

    public function navigationSort(int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }
}
