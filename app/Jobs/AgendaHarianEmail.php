<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;

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

            $tanggal = Carbon::now('Asia/Makassar')->format('j F Y');
            $email   = $this->user->username . '@bps.go.id';
            $subject = 'Agenda Rapat Anda Hari Ini - ' . $tanggal;

            // Render Blade view to HTML string
            $bodyHtml = view('emails.agenda_harian', [
                'user'     => $this->user,
                'meetings' => $this->meetings,
                'tanggal'  => $tanggal,
            ])->render();

            $relayUrl   = env('MAIL_RELAY_URL', '');
            $relayToken = env('MAIL_RELAY_TOKEN', '');
            $fromAddress = env('MAIL_FROM_ADDRESS', 'meetapp@bps.go.id');
            $fromName    = env('MAIL_FROM_NAME', 'MeetApp Kalsel');

            if (empty($relayUrl)) {
                \Log::error('AgendaHarianEmail: MAIL_RELAY_URL is not set.');
                return;
            }

            $payload = json_encode([
                'to'           => $email,
                'subject'      => $subject,
                'body_html'    => $bodyHtml,
                'from_address' => $fromAddress,
                'from_name'    => $fromName,
                'token'        => $relayToken,
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $relayUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
                'Accept: application/json, text/html, */*',
                'Accept-Language: en-US,en;q=0.9',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
            ]);
            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                \Log::error('AgendaHarianEmail cURL error for user ' . $this->user->id . ': ' . $curlError);
                return;
            }

            $result = json_decode($response, true);

            if ($httpCode !== 200 || (isset($result['status']) && $result['status'] !== 'sent')) {
                $errMsg = isset($result['message']) ? $result['message'] : $response;
                \Log::error('AgendaHarianEmail relay error for user ' . $this->user->id . ' (' . $email . '): ' . $errMsg);
            }

        } catch (\Exception $e) {
            \Log::error('AgendaHarianEmail failed for user ' . $this->user->id . ': ' . $e->getMessage());
        }
    }
}
