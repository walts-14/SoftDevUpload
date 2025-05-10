<?php

namespace App\Console;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $reminders = DB::table('document_reminders')
                ->whereDate('reminder_date', Carbon::today())
                ->get();
    
            foreach ($reminders as $reminder) {
                Mail::raw("Reminder: You need to submit the following missing documents: " . implode(", ", json_decode($reminder->missing_documents)), function ($message) use ($reminder) {
                    $message->to($reminder->email)
                            ->subject("Document Submission Reminder");
                });
    
                DB::table('document_reminders')->where('id', $reminder->id)->delete();
            }
        })->daily();
    }
}
