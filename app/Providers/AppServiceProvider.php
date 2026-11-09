<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Str::macro('cleanText', function (?string $value): string {
            if (empty($value)) {
                return '';
            }
            
            $value = preg_replace('/[\r\n\t]+/', ' ', $value);

            $value = trim($value);

            $value = preg_replace('/\s+/', ' ', $value);

            if ($options['remove_special_chars'] ?? false) {
                $value = preg_replace('/[^\w\s.,¡!¿?áéíóúÁÉÍÓÚñÑüÜ-]/u', '', $value);
            }

            return $value;
        });
    }
}
