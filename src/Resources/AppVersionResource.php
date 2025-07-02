<?php

namespace Alareqi\FilamentAppVersionManager\Resources;

use Alareqi\FilamentAppVersionManager\Resources\AppVersionResource\Pages;
use Alareqi\FilamentAppVersionManager\Enums\Platform;
use Alareqi\FilamentAppVersionManager\Models\AppVersion;
use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class AppVersionResource extends Resource
{
    protected static ?string $model = AppVersion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?int $navigationSort = 1;

    /**
     * Get configuration value through plugin system with fallback to config file
     * Always returns evaluated values (not closures) for use in form components
     */
    public static function getConfig(?string $key = null, mixed $default = null): mixed
    {
        try {
            // Try to get the plugin instance
            $plugin = FilamentAppVersionManagerPlugin::get();
            $result = $plugin->getConfig($key, $default);

            // Ensure closures are evaluated for form component usage
            if ($result instanceof \Closure) {
                $result = $result();
            }

            return $result;
        } catch (\Exception) {
            // Fallback to direct config access if plugin is not available
            if ($key === null) {
                return config('filament-app-version-manager');
            }
            $configValue = config("filament-app-version-manager.{$key}");
            // If config value is null, use the default
            $result = $configValue === null ? $default : $configValue;

            // Ensure closures are evaluated for form component usage
            if ($result instanceof \Closure) {
                $result = $result();
            }

            return $result;
        }
    }

    public static function getNavigationGroup(): ?string
    {
        $group = static::getConfig('navigation.group', __('filament-app-version-manager::app_version.navigation_group'));
        return $group ?? __('filament-app-version-manager::app_version.navigation_group');
    }

    public static function getNavigationIcon(): ?string
    {
        return static::getConfig('navigation.icon', 'heroicon-o-rocket-launch');
    }

    public static function getNavigationSort(): ?int
    {
        return static::getConfig('navigation.sort', 1);
    }

    public static function getModelLabel(): string
    {
        return __('filament-app-version-manager::app_version.singular_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-app-version-manager::app_version.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-app-version-manager::app_version.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament-app-version-manager::app_version.sections.version_info'))
                    ->schema([
                        Forms\Components\TextInput::make('version')
                            ->label(__('filament-app-version-manager::app_version.fields.version'))
                            ->placeholder(__('filament-app-version-manager::app_version.placeholders.version'))
                            ->helperText(__('filament-app-version-manager::app_version.help.version'))
                            ->required()
                            ->maxLength(static::getConfig('validation.max_version_length', 20))
                            ->rules([
                                'required',
                                'string',
                                'max:' . static::getConfig('validation.max_version_length', 20),
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    if (!AppVersion::validateSemanticVersion($value)) {
                                        $fail(__('filament-app-version-manager::app_version.validation.version_format'));
                                    }
                                },
                            ])
                            ->unique(AppVersion::class, 'version', ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                return $rule->where('platform', $get('platform'));
                            }),

                        Forms\Components\TextInput::make('build_number')
                            ->label(__('filament-app-version-manager::app_version.fields.build_number'))
                            ->placeholder(__('filament-app-version-manager::app_version.placeholders.build_number'))
                            ->helperText(__('filament-app-version-manager::app_version.help.build_number'))
                            ->maxLength(static::getConfig('validation.max_build_number_length', 50)),

                        Forms\Components\Select::make('platform')
                            ->label(__('filament-app-version-manager::app_version.fields.platform'))
                            ->helperText(__('filament-app-version-manager::app_version.help.platform'))
                            ->options(Platform::class)
                            ->required()
                            ->default(static::getConfig('defaults.platform', Platform::IOS))
                            ->reactive(),

                        Forms\Components\TextInput::make('minimum_required_version')
                            ->label(__('filament-app-version-manager::app_version.fields.minimum_required_version'))
                            ->placeholder(__('filament-app-version-manager::app_version.placeholders.minimum_required_version'))
                            ->helperText(__('filament-app-version-manager::app_version.help.minimum_required_version'))
                            ->maxLength(static::getConfig('validation.max_version_length', 20))
                            ->rules([
                                'nullable',
                                'string',
                                'max:' . static::getConfig('validation.max_version_length', 20),
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    if ($value && !AppVersion::validateSemanticVersion($value)) {
                                        $fail(__('filament-app-version-manager::app_version.validation.minimum_version_format'));
                                    }
                                },
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('filament-app-version-manager::app_version.sections.release_info'))
                    ->schema([
                        Forms\Components\DatePicker::make('release_date')
                            ->label(__('filament-app-version-manager::app_version.fields.release_date'))
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false),

                        Forms\Components\TextInput::make('download_url')
                            ->label(__('filament-app-version-manager::app_version.fields.download_url'))
                            ->placeholder(__('filament-app-version-manager::app_version.placeholders.download_url'))
                            ->helperText(__('filament-app-version-manager::app_version.help.download_url'))
                            ->url()
                            ->maxLength(static::getConfig('validation.max_download_url_length', 500))
                            ->columnSpanFull(),

                        Forms\Components\Tabs::make('release_notes_tabs')
                            ->label(__('filament-app-version-manager::app_version.fields.release_notes'))
                            ->tabs(static::getReleaseNotesLanguageTabs())
                            ->columnSpanFull()
                            ->visible(static::getConfig('features.multilingual_release_notes', true)),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('filament-app-version-manager::app_version.sections.settings'))
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('filament-app-version-manager::app_version.fields.is_active'))
                            ->helperText(__('filament-app-version-manager::app_version.help.is_active'))
                            ->default(static::getConfig('defaults.is_active', true)),

                        Forms\Components\Toggle::make('force_update')
                            ->label(__('filament-app-version-manager::app_version.fields.force_update'))
                            ->helperText(__('filament-app-version-manager::app_version.help.force_update'))
                            ->default(static::getConfig('defaults.force_update', false))
                            ->visible(static::getConfig('features.force_updates', true)),

                        Forms\Components\Toggle::make('is_beta')
                            ->label(__('filament-app-version-manager::app_version.fields.is_beta'))
                            ->helperText(__('filament-app-version-manager::app_version.help.is_beta'))
                            ->default(static::getConfig('defaults.is_beta', false))
                            ->visible(static::getConfig('features.beta_versions', true)),

                        Forms\Components\Toggle::make('is_rollback')
                            ->label(__('filament-app-version-manager::app_version.fields.is_rollback'))
                            ->helperText(__('filament-app-version-manager::app_version.help.is_rollback'))
                            ->default(static::getConfig('defaults.is_rollback', false))
                            ->visible(static::getConfig('features.version_rollback', true)),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('filament-app-version-manager::app_version.sections.metadata'))
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label(__('filament-app-version-manager::app_version.fields.metadata'))
                            ->helperText(__('filament-app-version-manager::app_version.help.metadata'))
                            ->keyLabel(__('filament-app-version-manager::app_version.fields.key'))
                            ->valueLabel(__('filament-app-version-manager::app_version.fields.value'))
                            ->columnSpanFull(),
                    ])
                    ->visible(static::getConfig('features.metadata_storage', true))
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->label(__('filament-app-version-manager::app_version.columns.version'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyMessage(__('filament-app-version-manager::app_version.messages.copied')),

                Tables\Columns\TextColumn::make('platform')
                    ->label(__('filament-app-version-manager::app_version.columns.platform'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('release_date')
                    ->label(__('filament-app-version-manager::app_version.columns.release_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament-app-version-manager::app_version.columns.is_active'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('force_update')
                    ->label(__('filament-app-version-manager::app_version.columns.force_update'))
                    ->boolean()
                    ->sortable()
                    ->toggleable()
                    ->visible(static::getConfig('features.force_updates', true)),

                Tables\Columns\IconColumn::make('is_beta')
                    ->label(__('filament-app-version-manager::app_version.columns.is_beta'))
                    ->boolean()
                    ->sortable()
                    ->toggleable()
                    ->visible(static::getConfig('features.beta_versions', true)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-app-version-manager::app_version.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament-app-version-manager::app_version.columns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->label(__('filament-app-version-manager::app_version.filters.platform'))
                    ->options(Platform::class),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament-app-version-manager::app_version.filters.is_active')),

                Tables\Filters\TernaryFilter::make('force_update')
                    ->label(__('filament-app-version-manager::app_version.filters.force_update'))
                    ->visible(static::getConfig('features.force_updates', true)),

                Tables\Filters\TernaryFilter::make('is_beta')
                    ->label(__('filament-app-version-manager::app_version.filters.is_beta'))
                    ->visible(static::getConfig('features.beta_versions', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('release_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get language tabs for multilingual release notes
     */
    protected static function getReleaseNotesLanguageTabs(): array
    {
        $supportedLocales = static::getConfig('localization.supported_locales', ['ar', 'en']);
        $tabs = [];

        // Ensure we have an array of locales
        if (!is_array($supportedLocales) || empty($supportedLocales)) {
            $supportedLocales = ['ar', 'en'];
        }

        foreach ($supportedLocales as $locale) {
            $languageLabel = static::getLanguageLabel($locale);

            $tabs[] = Forms\Components\Tabs\Tab::make($locale)
                ->label($languageLabel)
                ->schema([
                    Forms\Components\Textarea::make("release_notes.{$locale}")
                        ->label(__('filament-app-version-manager::app_version.fields.notes'))
                        ->helperText(__('filament-app-version-manager::app_version.help.release_notes'))
                        ->placeholder(__('filament-app-version-manager::app_version.placeholders.release_notes', ['language' => $languageLabel]))
                        ->rows(6)
                        ->maxLength(2000)
                        ->columnSpanFull()
                        ->default(''),
                ]);
        }

        return $tabs;
    }

    /**
     * Get human-readable language label for a locale code
     */
    protected static function getLanguageLabel(string $locale): string
    {
        $labels = [
            'ar' => 'العربية',
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'ko' => '한국어',
        ];

        return $labels[$locale] ?? strtoupper($locale);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppVersions::route('/'),
            'create' => Pages\CreateAppVersion::route('/create'),
            'edit' => Pages\EditAppVersion::route('/{record}/edit'),
        ];
    }
}
