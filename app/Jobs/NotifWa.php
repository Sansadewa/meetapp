<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ScheduleLogModel;

//Dear developer yang melanjutkan setelah saya, 
//kalau WA nya gajalan mungkin karena service php artisan queue:work lupa dijalankan di servicenya. 
//Cek di Services dengan nama notifwa_meetapp, nah itu pakai NSSM. berarti jalanin aja di cmd 'nssm edit notifwa_meetapp' untuk cek confignya
//Resume lagi di services kalau sudah selesai.
//Cheers, Gibran. Yang nyari kenapa gakjalan ternyata servicesnya paused wkwk. 22 November 2024.

class NotifWa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return $this->send($this->data['message'], $this->data['to']);
    }

    public function send($message, $tujuan)
	{
	    // $ch = curl_init(); 
	    // // set url 
	    // // http://bpskalsel.com/wabot/api/wa?to=085791927509&message=HaiLagi
	    // $nohp = $tujuan;
	    // curl_setopt($ch, CURLOPT_URL, "http://bpskalsel.com/wabot/api/wa");
		// curl_setopt($ch, CURLOPT_POST, 1);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, "to=".$nohp."&message=".$message);
	    // $output = curl_exec($ch); 
	    // curl_close($ch);
	    // return $output;

        $token = env('WA_CONNECT_TOKEN','');
        if(empty($token)){
            log::error('WA_CONNECT_TOKEN is not set');
            return;
        }
        $url  = 'https://app.waconnect.id/api/send_express';

        $headers  = [
            'Content-Type: application/x-www-form-urlencoded'
        ];



        $postData = [
            "number" => $tujuan,
            'message' => $message,
            'token' => $token,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));           
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
        $response = curl_exec($ch);
        $error_msg = curl_error($ch);

        curl_close($ch);
        //print_r($error_msg);

        //catat setiap kali jalan
        
        // log::error($response);
        return $response;
    }
}
