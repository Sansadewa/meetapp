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
                'body_html'    => base64_encode($bodyHtml),
                'from_address' => $fromAddress,
                'from_name'    => $fromName,
                'token'        => $relayToken,
            ]);

            // Randomize fingerprint to avoid WAF pattern detection
            $userAgent = $this->getRandomUserAgent();
            $acceptLang = $this->getRandomAcceptLang();
            $requestId = md5(uniqid(mt_rand(), true));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $relayUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_ENCODING, '');  // Accept any encoding (gzip, deflate)
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: ' . $acceptLang,
                'Cache-Control: max-age=0',
                'Connection: keep-alive',
                'X-Requested-With: XMLHttpRequest',
                'X-Request-ID: ' . $requestId,
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
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

    /**
     * Get a random User-Agent string from a pool of real browser agents
     */
    private function getRandomUserAgent()
    {
        $agents = array(
            // Chrome Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            // Chrome Mac
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            // Firefox Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:122.0) Gecko/20100101 Firefox/122.0',
            // Edge
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 Edg/121.0.0.0',
            // Safari Mac
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
        );

        return $agents[array_rand($agents)];
    }

    /**
     * Get a random Accept-Language header value
     */
    private function getRandomAcceptLang()
    {
        $langs = array(
            'en-US,en;q=0.9',
            'en-US,en;q=0.9,id;q=0.8',
            'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'en-GB,en;q=0.9,en-US;q=0.8',
            'id,en-US;q=0.9,en;q=0.8',
            'en-US,en;q=0.9,ms;q=0.8',
            'id-ID,id;q=0.9,en;q=0.8',
        );

        return $langs[array_rand($langs)];
    }
}
