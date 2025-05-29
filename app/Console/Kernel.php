<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Nettoyer les logs tous les jours à minuit
        $schedule->command('logs:clean')->daily();
        
        // Vérifier les licences expirées tous les jours à 1h du matin
        $schedule->command('licence:check-expired')->dailyAt('01:00');
        
        // Optimiser les images une fois par semaine (dimanche à 3h du matin)
        $schedule->command('images:optimize')->weekly()->sundays()->at('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
