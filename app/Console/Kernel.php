<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Mail;
use App\Models\User;
use App\Mail\PendingDocumentsReminder;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $usersWithPendingDocs = User::whereHas('documents', function ($query) {
                $query->where('status', 'pending');
            })->get();

            foreach ($usersWithPendingDocs as $user) {
                Mail::to($user->email)->send(new PendingDocumentsReminder($user));
            }
        })->everyFiveDays();
    }
}
