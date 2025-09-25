<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function ($schedule) {
        // Update member credits monthly on the 1st
        $schedule->command('members:update-credits')->monthlyOn(1, '00:00');
        
        // Ensure all members have login access - run weekly
        $schedule->command('members:ensure-login-access')->weeklyOn(1, '02:00'); // Monday at 2 AM
        
        // Ensure all subscription holders have proper accounts - run weekly
        $schedule->command('members:ensure-subscription-users')->weeklyOn(1, '02:30'); // Monday at 2:30 AM
        
        // Sync Stripe subscriptions with local accounts - run weekly
        $schedule->command('members:sync-stripe-subscriptions --create-missing')->weeklyOn(1, '03:00'); // Monday at 3 AM
        
        // Verify all memberships have proper accounts - run daily
        $schedule->command('members:verify-accounts')->daily('04:00');

        // Send instructor class roster reminders ~1 hour before class start
        $schedule->command('classes:send-instructor-reminders')->everyMinute();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // register global/route middleware here if you need
        // e.g. $middleware->alias(['role' => \App\Http\Middleware\RoleMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
