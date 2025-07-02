<?php

namespace Alareqi\FilamentAppVersionManager\Commands;

use Illuminate\Console\Command;

class FilamentAppVersionManagerCommand extends Command
{
    public $signature = 'filament-app-version-manager:install';

    public $description = 'Install the Filament App Version Manager plugin';

    public function handle(): int
    {
        $this->comment('Installing Filament App Version Manager...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'filament-app-version-manager-config',
            '--force' => true,
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'filament-app-version-manager-migrations',
            '--force' => true,
        ]);

        // Publish translations
        $this->call('vendor:publish', [
            '--tag' => 'filament-app-version-manager-translations',
            '--force' => true,
        ]);

        $this->info('Filament App Version Manager installed successfully!');

        if ($this->confirm('Would you like to run the migrations now?')) {
            $this->call('migrate');
        }

        if ($this->confirm('Would you like to seed sample app versions?')) {
            $this->call('db:seed', [
                '--class' => 'Alareqi\\FilamentAppVersionManager\\Database\\Seeders\\AppVersionSeeder',
            ]);
        }

        $this->comment('Please add the plugin to your Filament panel provider:');
        $this->line('');
        $this->line('use Alareqi\\FilamentAppVersionManager\\FilamentAppVersionManagerPlugin;');
        $this->line('');
        $this->line('public function panel(Panel $panel): Panel');
        $this->line('{');
        $this->line('    return $panel');
        $this->line('        // ... other configuration');
        $this->line('        ->plugins([');
        $this->line('            FilamentAppVersionManagerPlugin::make(),');
        $this->line('        ]);');
        $this->line('}');

        return self::SUCCESS;
    }
}
