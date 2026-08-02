<?php

namespace App\Providers;

use Filament\Resources\Resource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Srpski: bez automatskog "Title Case" — samo prvo slovo veliko.
        Resource::titleCaseModelLabel(false);
    }
}
