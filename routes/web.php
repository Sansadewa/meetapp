<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(["middleware" => "is_authenticated"], function() {
    Route::get('/', 'meetController@getHomePage')->name('home')->secure();
    Route::get('/login', function() {
        return view('login.login');
    });

    Route::post('/login', 'LoginController@login');
    Route::post('/login', 'LoginController@login');

    Route::get('/getzoomtoken', 'zoomController@getFirstToken');
    Route::get('/listmeetingZoom', 'zoomController@listmeetingZoom');

    Route::get('/rapat', 'meetController@getBuatRapatPage');
    Route::get('/lists', 'meetController@getDaftarRapatPage');
    Route::get('/today', 'meetController@getTodayPage');
    Route::get('/upload-notulensi', 'meetController@getUploadNotulensiPage');
    Route::get('/request-zoom', 'meetController@getRequestZoomPage');
    Route::get('/get-unit-kerja', 'meetController@getUnitKerja');
    Route::get('/get-ruang', 'meetController@getRuang');
    Route::get('/search-attendees', 'meetController@searchAttendees');

    Route::post('/tambah-rapat', 'meetController@tambahRapat');
    Route::get('/get-data-rapat', 'meetController@getRapat');
    Route::post('/get-data-rapat-edit', 'meetController@getDataRapatEdit');
    Route::post('/edit-rapat', 'meetController@editRapat');
    Route::post('/edit-tangal-rapat-drag', 'meetController@editTanggalRapatDrag');
    Route::post('/edit-tangal-rapat-resize', 'meetController@editTanggalRapatResize');
    Route::post('/hapus-rapat', 'meetController@hapusRapat');

    Route::get('/get-rapat', 'meetController@getRapatAll');
    Route::post('/get-detail-zoom', 'meetController@getDetailZoom');
    Route::post('/upload-notulensi', 'meetController@uploadNotulensi');
    Route::post('/hapus-notulensi', 'meetController@hapusNotulensi');
    Route::get('/download-notulensi', 'meetController@downloadNotulensi');
    Route::post('/detail-rapat', 'meetController@getDetailRapat');
    Route::post('/save-zoom-rapat', 'meetController@saveZoomRapat');
    Route::post('/get-zoom-rinc', 'meetController@getZoomRinc');
    Route::post('/save-edit-zoom', 'meetController@saveEditZoom');
    Route::post('/get-data-grafik', 'meetController@getDataGrafik');
    Route::post('/send-notif-zoom', 'meetController@sendNotifZoom');
    Route::post('/get-pj-zoom', 'meetController@getPJZoom');

    Route::get('/get-calon-host', 'meetController@getCalonHost');
    Route::post('/get-zoom-host', 'meetController@getZoomHost');
    Route::post('/save-edit-host', 'meetController@editHost');
    // Route::get('/cek123', function(){
    //     echo json_encode(array('result' => 'ok'));
    // });
});
// Public routes (no authentication required)
Route::get('/meeting/{uid}', 'meetController@showMeetingByUid');
Route::get('/meeting/{uid}/download', 'meetController@downloadNotulensiByUid');
Route::get('/s/{uid}', 'meetController@showMeetingByUid');
Route::get('/s/{uid}/download', 'meetController@downloadNotulensiByUid');

Route::get('/notulensi/download/{filename}', 'meetController@downloadNotulensiFile')
    ->name('notulensi.download');
    Route::get('/notulensi/download/', 'LoginController@login');
Route::get('/maintenance', function () {
    return view('maintenance'); // Ensure resources/views/maintenance.blade.php exists
});
Route::get('/reminder-ruang-rapat', 'meetController@reminderRuangRapat');
Route::get('/reminder-host-zoom', 'meetController@reminderHostZoomAll');
Route::get('/callback-zoom', 'zoomController@callbackZoom');
Route::get('/tes-dispatch', 'meetController@tesDispatch');
Route::get('/logout', function(){
    session()->flush();
    return redirect('/login');
});