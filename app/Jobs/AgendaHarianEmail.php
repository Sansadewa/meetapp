<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use App\Mail\AgendaHarian;

class AgendaHarianEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $meetings;

    public function __construct($user, $meetings)
    {
        $this->user = $user;
        $this->meetings = $meetings;
    }

    public function handle()
    {
        try {
            $delayMs = (int) env('MAIL_SEND_DELAY_MS', 0);
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
            $email = $this->user->username . '@bps.go.id';
            Mail::to($email)->send(new AgendaHarian($this->user, $this->meetings));
        } catch (\Exception $e) {
            \Log::error('AgendaHarianEmail failed for user ' . $this->user->id . ': ' . $e->getMessage());
        }
    }
}
