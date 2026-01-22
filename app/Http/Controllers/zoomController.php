<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Users_model;
use App\RapatModel;
use App\ZoomModel;
use App\Services\Zoom;

class zoomController extends Controller
{
    public function getFirstToken() {
        $zoom = new Zoom([
        'client_id' => env('ZOOM_CLIENT_ID1'),
        'client_secret' => env('ZOOM_CLIENT_SECRET1'),
        'redirect_uri' => 'https://bpskalsel.com/meetapp/callback-zoom',
        'credential_path' =>  env('ZOOM_CREDENTIALS_PATH1')
        ]);

        $oAuthURL = $zoom->oAuthUrl();
        echo "<a href='{$oAuthURL}'>{$oAuthURL}</a><br>";
    }

    public function callbackZoom() {
        $zoom = new Zoom([
            'client_id' => env('ZOOM_CLIENT_ID1'),
            'client_secret' => env('ZOOM_CLIENT_SECRET1'),
            'redirect_uri' => 'https://bpskalsel.com/meetapp/callback-zoom',
            'credential_path' =>  env('ZOOM_CREDENTIALS_PATH1')
        ]);
        
        // save oauth code
        if(isset($_GET['code'])){
            $is_token_saved = $zoom->token($_GET['code']);
            print_r($is_token_saved);
            }
    
            print_r($zoom->listMeeting());
    }

    public function listmeetingZoom(){
        $zoom = new Zoom([
            'client_id' => env('ZOOM_CLIENT_ID1'),
            'client_secret' => env('ZOOM_CLIENT_SECRET1'),
            'redirect_uri' => 'https://bpskalsel.com/meetapp/callback-zoom',
            'credential_path' =>  env('ZOOM_CREDENTIALS_PATH1')
        ]);
        echo "<pre>";
        print_r($zoom->listMeeting());

    }
}