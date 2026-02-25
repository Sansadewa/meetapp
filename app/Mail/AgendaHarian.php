<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AgendaHarian extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $meetings;

    public function __construct($user, $meetings)
    {
        $this->user = $user;
        $this->meetings = $meetings;
    }

    public function build()
    {
        $tanggal = \Carbon\Carbon::now('Asia/Makassar')->format('j F Y');
        
        return $this->subject('Agenda Rapat Anda Hari Ini - ' . $tanggal)
                    ->view('emails.agenda_harian')
                    ->with([
                        'user' => $this->user,
                        'meetings' => $this->meetings,
                        'tanggal' => $tanggal,
                    ]);
    }
}
