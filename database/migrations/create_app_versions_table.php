<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Alareqi\FilamentAppVersionManager\Enums\Platform;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = config('filament-app-version-manager.database.table_name', 'app_versions');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('version', 20)->index();
            $table->string('build_number', 50)->nullable();
            $table->enum('platform', Platform::values())->index();
            $table->string('minimum_required_version', 20)->nullable();
            $table->json('release_notes')->nullable();
            $table->date('release_date')->index();
            $table->string('download_url', 500)->nullable();
            $table->boolean('force_update')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_beta')->default(false)->index();
            $table->boolean('is_rollback')->default(false)->index();
            $table->json('metadata')->nullable();

            // Audit fields - these will be nullable to support cases where admin model doesn't exist
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Unique constraint: one version per platform
            $table->unique(['version', 'platform'], 'unique_version_platform');

            // Indexes for common queries
            $table->index(['platform', 'is_active', 'is_beta'], 'platform_active_beta_index');
            $table->index(['release_date', 'is_active'], 'release_date_active_index');

            // Foreign key constraints (only if admin table exists)
            if (Schema::hasTable('admins')) {
                $table->foreign('created_by')->references('id')->on('admins')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('admins')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('filament-app-version-manager.database.table_name', 'app_versions');
        Schema::dropIfExists($tableName);
    }
};
