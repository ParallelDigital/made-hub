<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ResetMonthlyCredits;

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
        // Schedule the monthly credits reset on the first day of each month at 00:00
        Schedule::command('credits:reset-monthly')
            ->monthlyOn(1, '00:00')
            ->timezone('Europe/London')
            ->onOneServer();
    }
}
