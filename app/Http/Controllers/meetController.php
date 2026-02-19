<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\UnitKerjaModel;
use App\RuangModel;
use App\RapatModel;
use App\RapatCustomField;
use App\NotulensiModel;
use App\ZoomModel;
use App\UserModel;
use App\ScheduleLogModel;
use App\Jobs\Notifwa;
use App\NotifAdmin;
use App\NotifUmum;
use Users_model;
use Zipper;
use Exception;

class meetController extends Controller
{

    public function getTodayPage(Request $request)
    {
        // Get today's date
        $today = date('Y-m-d');

        // Fetch all meetings
        $rapat = RapatModel::select('id', 'nama', 'tanggal_rapat_start', 'tanggal_rapat_end', 'ruang_rapat', 'waktu_mulai_rapat', 'waktu_selesai_rapat')
                   ->whereDate('tanggal_rapat_end', '>=', $today)
                   ->whereDate('tanggal_rapat_start', '<=', $today)
                   ->get();

        // Fetch all rooms
        $ruang = RuangModel::where('visible_ruang', '=', 1)->get();

        // Filter and group meetings by room and include recurring meetings
        $meetingsByRoom = [];

        foreach ($ruang as $ruangan) {
            $meetingsToday = $rapat->filter(
                function ($meeting) use ($ruangan, $today) {
                    return $meeting->ruang_rapat == $ruangan->nama_ruang;
                });
    
            $meetingsByRoom[$ruangan->id_ruang] = $meetingsToday;
        }

        return view('pages.today', compact('ruang', 'meetingsByRoom'));
    }

    public function getHomePage(Request $request)
    {
       // Get today's date
       $today = date('Y-m-d');
        // Get current user ID from session
        $user_id = session('user_id');
        
        // DEBUG: Log received data
        // Log::info('=== getHomePage DEBUG START ===');
        // Log::info('Today date: ' . $today);
        // Log::info('User ID from session: ' . ($user_id ? $user_id : 'NULL'));
        // Log::info('Request data: ' . json_encode($request->all()));
                
        if (!$user_id) {
            // If no user logged in, return empty meetings
            Log::info('No user_id found, returning empty meetings');
            $userMeetings = collect([]);
            return view('pages.home', compact('userMeetings'));
        }
        // Get user's unit_kerja IDs (for current year)
        $user = UserModel::find($user_id);
        $unit_kerja_ids = [];
        if ($user) {
            $unit_kerjas = $user->unitKerja()->wherePivot('tahun', date('Y'))->get();
            $unit_kerja_ids = $unit_kerjas->pluck('id')->toArray();
            // Log::info('User found: ' . ($user->name ? $user->name : 'N/A'));
            // Log::info('Unit Kerja IDs: ' . json_encode($unit_kerja_ids));
            // Log::info('Unit Kerja count: ' . count($unit_kerja_ids));
        } else {
            Log::info('User not found with ID: ' . $user_id);
        }

        // Query meetings where user is assigned (directly or through unit_kerja)
        $queryBuilder = RapatModel::select('rapat.id', 'rapat.uid','rapat.nama', 'rapat.tanggal_rapat_start', 'rapat.tanggal_rapat_end', 
                                           'rapat.ruang_rapat', 'rapat.waktu_mulai_rapat', 'rapat.waktu_selesai_rapat')
            ->join('rapat_user', 'rapat_user.rapat_id', '=', 'rapat.id')
            ->where(function($query) use ($user_id, $unit_kerja_ids) {
                // Direct user assignment
                $query->where(function($q) use ($user_id) {
                    $q->where('rapat_user.attendee_type', 'App\UserModel')
                      ->where('rapat_user.attendee_id', $user_id);
                });

        // Unit kerja assignment (if user has unit_kerjas)
        if (!empty($unit_kerja_ids)) {
            $query->orWhere(function($q) use ($unit_kerja_ids) {
                $q->where('rapat_user.attendee_type', 'App\UnitKerjaModel')
                  ->whereIn('rapat_user.attendee_id', $unit_kerja_ids);
            });
        }
        })
        ->whereDate('rapat.tanggal_rapat_end', '>=', $today)
        ->whereDate('rapat.tanggal_rapat_start', '<=', $today)
        ->groupBy('rapat.id', 'rapat.uid','rapat.nama', 'rapat.tanggal_rapat_start', 'rapat.tanggal_rapat_end', 
                     'rapat.ruang_rapat', 'rapat.waktu_mulai_rapat', 'rapat.waktu_selesai_rapat')
        ->orderBy('rapat.waktu_mulai_rapat', 'asc');

        // DEBUG: Log the SQL query
        // $sql = $queryBuilder->toSql();
        // $bindings = $queryBuilder->getBindings();
        // Log::info('SQL Query: ' . $sql);
        // Log::info('Query Bindings: ' . json_encode($bindings));
        
        // Execute query
        $userMeetings = $queryBuilder->get();
        
        // DEBUG: Log query results
        // Log::info('Meetings found: ' . $userMeetings->count());
        // Log::info('Meetings data: ' . json_encode($userMeetings->toArray()));
        // Log::info('=== getHomePage DEBUG END ===');

        // DEBUG: Uncomment below to dump and die (for immediate debugging)
        // dd([
        //     'today' => $today,
        //     'user_id' => $user_id,
        //     'unit_kerja_ids' => $unit_kerja_ids,
        //     'sql' => $sql,
        //     'bindings' => $bindings,
        //     'meetings_count' => $userMeetings->count(),
        //     'meetings' => $userMeetings->toArray()
        // ]);

        // Return the home view with today's meetings for the logged-in user
        return view('pages.home', compact('userMeetings'));
    }

    public function grafikRapat( $tahun = '' )
    {
        $uk_user = session('unit_kerja');
        $lv_user = session('level');
        $tahun = $tahun == '' ? date('Y') : $tahun;
        $rapat = RapatModel::select('rapat.*', DB::raw('MONTH(rapat.tanggal_rapat_start) as bulan_rapat'))
                ->whereRaw('YEAR(rapat.tanggal_rapat_start) = '.$tahun)
                ->where(function($query) use ($uk_user, $lv_user) {
                        if($lv_user != '2')
                        {
                            $query->where('unit_kerja', $uk_user);
                        }
                    })
                ->get();
        $grouped = $rapat->groupBy('bulan_rapat');
        $grouped->toArray();
        $rapat_perbulan = array(
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '6' => 0,
            '7' => 0,
            '8' => 0,
            '9' => 0,
            '10' => 0,
            '11' => 0,
            '12' => 0            

        );
        foreach ($grouped as $key => $value) {
            $rapat_perbulan[$key] = count($value);
        }
        return $rapat_perbulan;
    }

    public function getDataGrafik(Request $request)
    {
        echo json_encode($this->grafikRapat($request->data));
    }

    public function grafikRapatTahunan()
    {
        $uk_user = session('unit_kerja');
        $lv_user = session('level');

        $rapat = RapatModel::select('rapat.*', DB::raw('YEAR(rapat.tanggal_rapat_start) as tahun_rapat'))
                ->where(function($query) use ($uk_user, $lv_user) {
                        if($lv_user != '2')
                        {
                            $query->where('unit_kerja', $uk_user);
                        }
                    })
                ->get();
        $grouped = $rapat->groupBy('tahun_rapat');
        $grouped->toArray();
        $rapat_perbulan = array(
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '6' => 0,
            '7' => 0,
            '8' => 0,
            '9' => 0,
            '10' => 0,
            '11' => 0,
            '12' => 0            

        );
        foreach ($grouped as $key => $value) {
            $rapat_perbulan[$key] = count($value);
        }
        return $rapat_perbulan;
    }

    public function getDataGrafikTahunan(Request $request)
    {
        echo json_encode($this->grafikRapatTahunan($request->data));
    }

    public function getBuatRapatPage(Request $request)
    {
        $data = array(
            'unit_kerja' => UnitKerjaModel::all()
        );
        return view('pages.buatrapat', $data);
    }

    public function getDaftarRapatPage(Request $request)
    {
        return view('pages.daftarrapat');
    }

    public function getUploadNotulensiPage(Request $request)
    {
        return view('pages.uploadnotulensi');
    }

    public function getRequestZoomPage(Request $request)
    {        
        $data = array(            
            'merge' => $this->getUnsetZoomRequest()
        );
        return view('pages.requestzoom', $data);
    }

    public function getUnsetZoomRequest()
    {
        $daftar_request = RapatModel::select('rapat.*', 'unit_kerja.nama as nama_unit_kerja', DB::raw('(DATEDIFF(rapat.tanggal_rapat_end, rapat.tanggal_rapat_start) + 1) as dif'))
                                ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')                                
                                ->where('use_zoom', '1')
                                ->get();
        $daftar_zoom = ZoomModel::select('rapat', DB::raw('COUNT(rapat) as jumlah_zoom_id'))->groupBy('rapat')->get();
        $merge = $daftar_request->filter(function($value, $key) use ($daftar_zoom) {
            $filter_temp = $daftar_zoom->filter(function($temp) use ($value) { 
                                return $temp->rapat == $value->id; 
                           })->first();                        
            return ($filter_temp ? ($value->dif == $filter_temp->jumlah_zoom_id ? false : true) : true);            
        });
        return $merge;
    }

    public function getUnitKerja(Request $request)
    {
        // Get user ID from request or session
        $user_id = $request->input('user_id', session('user_id'));
        
        if ($user_id) {
            // Get user and their associated unit kerja for the current year
            $user = UserModel::find($user_id);
            if($user->level == '2') {
                $unit_kerja = UnitKerjaModel::all();
            } else {
                $unit_kerja = $user->unitKerja()->wherePivot('tahun', date('Y'))->get();
            }
        } else {
            // Fallback to none if no user_id
            $unit_kerja = null;
        }
        
        echo json_encode(['result' => $unit_kerja, 'uk_ses' => session('unit_kerja'), 'no_hp_ses' => session('no_hp')]);
    }

    public function getRuang(Request $request)
    {        
        $ruang = RuangModel::where('visible_ruang', '=', 1)->get()->toArray();
        echo json_encode(['result' => $ruang, 'uk_ses' => session('unit_kerja'), 'no_hp_ses' => session('no_hp')]);
    }

    public function searchAttendees(Request $request)
    {
        $query = $request->input('q', '');
        $results = array();
        
        if (strlen($query) >= 2) {
            // Search users by nama
            $users = UserModel::where(function($q) use ($query) {
                    $q->where('nama', 'like', '%' . $query . '%')
                      ->orWhere('username', 'like', '%' . $query . '%');
                })
                ->where('is_active', 1)
                ->limit(10)
                ->get();
            
            foreach ($users as $user) {
                $results[] = array(
                    'id' => 'user-' . $user->id,
                    'text' => $user->nama,
                    'type' => 'user'
                );
            }
            
            // Search unit_kerja by nama
            $unitKerja = UnitKerjaModel::where('nama', 'like', '%' . $query . '%')
                // ->where('tahun', 2023)
                ->where('tahun', date('Y'))
                ->limit(10)
                ->get();
            
            foreach ($unitKerja as $uk) {
                $results[] = array(
                    'id' => 'unit_kerja-' . $uk->id,
                    'text' => $uk->nama,
                    'type' => 'unit_kerja'
                );
            }
        }
        
        echo json_encode($results);
    }

    public function cekOverlap($data) {

        // Normalize date fields to Y-m-d format
        if (isset($data['tanggal_mulai'])) {
            try {
                $data['tanggal_mulai'] = (new \DateTime($data['tanggal_mulai']))->format('Y-m-d');
            } catch (Exception $e) {
                http_response_code(400);
                echo "Format tanggal_mulai tidak valid.";
                exit();
            }
        }
        if (isset($data['tanggal_selesai'])) {
            try {
                $data['tanggal_selesai'] = (new \DateTime($data['tanggal_selesai']))->format('Y-m-d');
            } catch (Exception $e) {
                http_response_code(400);
                echo "Format tanggal_selesai tidak valid.";
                exit();
            }
        }
        

        if(isset($data['tanggal_mulai'])) $start_date = $data['tanggal_mulai'];
        else $start_date = $data['tanggal_rapat_start'];

        if(isset($data['tanggal_selesai'])) $end_date = $data['tanggal_selesai'];
        else $end_date = $data['tanggal_rapat_end'];
        
        // If time fields not provided (from resize/drag), get from existing rapat record
        if (!isset($data['mulai_rapat']) || !isset($data['akhir_rapat'])) {
            $existingRapat = RapatModel::find($data['rapat']);
            $start_time = $data['mulai_rapat'] ?? $existingRapat->waktu_mulai_rapat;
            $end_time = $data['akhir_rapat'] ?? $existingRapat->waktu_selesai_rapat;
        } else {
            $start_time = $data['mulai_rapat'];
            $end_time = $data['akhir_rapat'];
        }
        //querynya. Silahkan pahami sendiri. Mudah kok, gunakan chatGPT wkwkwk. Intinya cek overlap.
        return RapatModel::select('id','nama','ruang_rapat', 'tanggal_rapat_start', 'waktu_mulai_rapat', 'tanggal_rapat_end', 'waktu_selesai_rapat')
        ->where(function($query) use ($start_date, $end_date, $start_time, $end_time) {
            $query->where(function($q) use ($start_date, $end_date, $start_time) {
                $q->where('tanggal_rapat_start', '<', $end_date)
                ->where('tanggal_rapat_end', '>', $start_date);
            })
            ->orWhere(function($q) use ($end_date, $start_time) {
                $q->where('tanggal_rapat_start', $end_date)
                ->where('waktu_selesai_rapat', '>', $start_time);
            })
            ->orWhere(function($q) use ($start_date, $start_time) {
                $q->where('tanggal_rapat_end', $start_date)
                ->where('waktu_selesai_rapat', '>', $start_time);
            });
        })
        ->where(function($query) use ($start_date, $end_date, $start_time, $end_time) {
            $query->where(function($q) use ($start_time, $end_time) {
                $q->where('waktu_mulai_rapat', '<', $end_time)
                ->where('waktu_selesai_rapat', '>', $start_time);
            })
            ->orWhere(function($q) use ($start_date, $end_time) {
                $q->where('waktu_mulai_rapat', $end_time)
                ->where('tanggal_rapat_end', '>', $start_date);
            })
            ->orWhere(function($q) use ($start_date, $start_time) {
                $q->where('waktu_selesai_rapat', $start_time)
                ->where('tanggal_rapat_end', '>', $start_date);
            });
        })
        ->where('ruang_rapat', $data['ruang_rapat'])
        ->get();
    }
    public function tambahRapat(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $date_created = date('Y-m-d H:i:s', strtotime("+1 hours"));
        $data = $request->data;

        //Cek dulu apakah dia barengan sama rapat lain (kalau bukan zoom)
        if($data['ruang_rapat'] != "Zoom Meeting Room Only"){
            $rapatoverlap = $this->cekOverlap($data);
                            
            //kalau ada, berarti kirim 409. yaitu ada konflik database.
            if ($rapatoverlap->count() > 0) {
                http_response_code(409);
                // echo json_encode(array('result' => 'error', 'namarapat' => $rapatoverlap->first()->nama, 'tanggalrapat' => $rapatoverlap->first()->tanggal_rapat_start));
                echo "Rapat anda bentrok dengan rapat ".$rapatoverlap->first()->nama." di tanggal ".  $rapatoverlap->first()->tanggal_rapat_start."<br><br>Hubungi Admin apabila ini sebuah kesalahan.";
                //force exit biar ga kekirim ke db
                exit();
            }
        }

        $rapat = new RapatModel;
        $rapat->uid = $this->generateUniqueUid(); // Generate unique UID
        $rapat->unit_kerja = $data['unit_kerja'];
        $rapat->ruang_rapat = $data['ruang_rapat'];
        $rapat->jumlah_peserta = $data['jumlah_peserta'];
        $rapat->nama = $data['nama_rapat'];
        $rapat->topik = $data['topik_rapat'];
        $rapat->tanggal_rapat_start = $data['tanggal_mulai'];
        $rapat->tanggal_rapat_end = $data['tanggal_selesai'];
        $rapat->waktu_mulai_rapat = $data['mulai_rapat'];
        $rapat->waktu_selesai_rapat = $data['akhir_rapat'];        
        // $rapat->use_zoom = $data['is_use_zoom'] == 'true' ? 1 : 0;
        $rapat->use_zoom = $data['is_use_zoom'];
        $rapat->created_at = $date_created;
        $rapat->created_by = session('user_id');
        $rapat->nohp_pj = $data['is_use_zoom'] == '1' ? $data['nomor_wa'] : null;
        $rapat->save();   
        
        // Save attendees
        if (isset($data['attendees']) && is_array($data['attendees'])) {
            foreach ($data['attendees'] as $attendeeId) {
                // Parse the composite ID (e.g., 'user-1' or 'unit_kerja-5')
                if (strpos($attendeeId, 'user-') === 0) {
                    $userId = str_replace('user-', '', $attendeeId);
                    DB::table('rapat_user')->insert([
                        'rapat_id' => $rapat->id,
                        'attendee_id' => $userId,
                        'attendee_type' => 'App\UserModel'
                    ]);
                } elseif (strpos($attendeeId, 'unit_kerja-') === 0) {
                    $unitKerjaId = str_replace('unit_kerja-', '', $attendeeId);
                    DB::table('rapat_user')->insert([
                        'rapat_id' => $rapat->id,
                        'attendee_id' => $unitKerjaId,
                        'attendee_type' => 'App\UnitKerjaModel'
                    ]);
                }
            }
        }
        
        // Save custom fields
        if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
            foreach ($data['custom_fields'] as $index => $field) {
                if (!empty($field['key']) && !empty($field['value'])) {
                    RapatCustomField::create([
                        'rapat_id' => $rapat->id,
                        'field_key' => $field['key'],
                        'field_value' => $field['value'],
                        'field_order' => $index
                    ]);
                }
            }
        }
        
        if($data['is_use_zoom'] == '1')
        {
            /* kirim notifikasi ke admin kalo ada permintaan zoom meeting
            *  
            *
            */

            $admin = NotifAdmin::all();            
            $unit_kerja_temp = UnitKerjaModel::find($data['unit_kerja']);
            foreach ($admin as $admin) {
                $message = '🚨*[Notifikasi MeetApp Kalsel] - Req Zoom*🚨  %0A  %0A Halo *'.$admin->nama.'*, %0A Ada permintaan Zoom Meeting dari *'.session('nama').''.(session('nama_unit_kerja') ? ' - '.session('nama_unit_kerja') : '').'* pada tanggal '.date("j F Y").'.';    
                $message .= ' Berikut merupakan rincian permintaan zoom meeting tersebut: %0A  %0A ';
                $message .= ' %0A Unit Kerja: '.$unit_kerja_temp->nama;
                $message .= ' %0A Nama rapat: '.$data['nama_rapat'];
                $message .= ' %0A Ruang rapat: '.$data['ruang_rapat'];
                $message .= ' %0A Topik rapat: '.$data['topik_rapat'];
                $date_temp = (new \DateTime($data['tanggal_mulai']))->diff(new \DateTime($data['tanggal_selesai']))->format('%a') > 0 ? date('j F Y', strtotime($data['tanggal_mulai'])).' - '.date('j F Y', strtotime($data['tanggal_selesai'])) : date('j F Y', strtotime($data['tanggal_mulai']));
                $message .= ' %0A Tanggal Rapat: '.$date_temp;
                $message .= ' %0A Jam Rapat: '.$data['mulai_rapat'].' - '.$data['akhir_rapat'];
                $message .= ' %0A Jumlah Peserta: '.$data['jumlah_peserta'].' %0A ';
                
                $wa = array(
                    'message' => urldecode($message),
                    'to' => $admin->no_wa
                );
                dispatch(new NotifWa($wa));

            }

        }
        if($data['ruang_rapat']!='Zoom Meeting Room Only'){
        
            $admin = NotifUmum::all();            
            $unit_kerja_temp = UnitKerjaModel::find($data['unit_kerja']);
            foreach ($admin as $admin) {
                $message = '*[Notifikasi MeetApp Kalsel]* %0A  %0A Halo *'.$admin->nama.'*, %0A Ada pembuatan rapat dari *'.session('nama').''.(session('nama_unit_kerja') ? ' - '.session('nama_unit_kerja') : '').'* pada tanggal '.date("j F Y").'.';    
                $message .= ' Berikut merupakan rincian rapat tersebut: %0A ';
                $message .= ' %0A Unit Kerja: '.$unit_kerja_temp->nama;
                $message .= ' %0A Nama rapat: '.$data['nama_rapat'];
                $message .= ' %0A Ruang rapat: '.$data['ruang_rapat'];
                $message .= ' %0A Topik rapat: '.$data['topik_rapat'];
                $date_temp = (new \DateTime($data['tanggal_mulai']))->diff(new \DateTime($data['tanggal_selesai']))->format('%a') > 0 ? date('j F Y', strtotime($data['tanggal_mulai'])).' - '.date('j F Y', strtotime($data['tanggal_selesai'])) : date('j F Y', strtotime($data['tanggal_mulai']));
                $message .= ' %0A Tanggal Rapat: '.$date_temp;
                $message .= ' %0A Jam Rapat: '.$data['mulai_rapat'].' - '.$data['akhir_rapat'];
                $message .= ' %0A Jumlah Peserta: '.$data['jumlah_peserta'].' %0A ';

                $wa = array(
                    'message' => urldecode($message),
                    'to' => $admin->no_wa
                );
                dispatch(new NotifWa($wa));

            }
        }
        
        echo json_encode(array('result' => 'sukses', 'rapat' => $rapat->id));
    }

     public function getRapat(Request $request)
     {
          // Get date range from FullCalendar
          $start = $request->input('start');
          $end = $request->input('end');
          
          $query = RapatModel::select(
                 'rapat.id',
                 'rapat.nama',
                 'rapat.tanggal_rapat_start',
                 'rapat.tanggal_rapat_end',
                 'rapat.waktu_mulai_rapat',
                 'rapat.waktu_selesai_rapat',
                 'rapat.use_zoom',
                 'rapat.unit_kerja',
                 'rapat.ruang_rapat',
                 'unit_kerja.singkatan as singkatan_unit_kerja',
                 'unit_kerja.class_bg'
             )
                 ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja');
          
          // Apply date range filter if provided
          if ($start && $end) {
              $query->where(function($q) use ($start, $end) {
                  // Events that overlap with the requested range
                  $q->where('rapat.tanggal_rapat_start', '<=', $end)
                    ->where('rapat.tanggal_rapat_end', '>=', $start);
              });
          }
          
          $rapat = $query->get();
          
          // Get user's unit_kerja IDs for current year
          $user_unit_kerja_ids = [];
          $user = UserModel::find(session('user_id'));
          if ($user && $user->level != '2') {
              $user_unit_kerja_ids = $user->unitKerja()
                  ->wherePivot('tahun', date('Y'))
                  ->pluck('unit_kerja.id')
                  ->toArray();
          }
          
          echo json_encode(array(
              'result' => $rapat, 
              'uk_ses' => $user_unit_kerja_ids,
              'lvl_ses' => session('level')
          ));
     }

    public function getDataRapatEdit(Request $request)
    {
         $rapat = RapatModel::select('rapat.*', 'unit_kerja.nama as nama_unit_kerja', 'unit_kerja.singkatan as singkatan_unit_kerja', 'unit_kerja.class_bg')
                ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')
                ->where('rapat.id', $request->data)
                ->first();
         $attendees = array();
         $customFields = array();
         if ($rapat) {
             $attendees = $rapat->getAllAttendees();
             $customFields = RapatCustomField::where('rapat_id', $rapat->id)
                             ->orderBy('field_order', 'asc')
                             ->get()
                             ->toArray();
         }
         
         // Get user's unit_kerja IDs for current year
         $user_unit_kerja_ids = [];
         $user = UserModel::find(session('user_id'));
         if ($user && $user->level != '2') {
             $user_unit_kerja_ids = $user->unitKerja()
                 ->wherePivot('tahun', date('Y'))
                 ->pluck('id')
                 ->toArray();
         }
         
         echo json_encode(array(
             'result' => $rapat, 
             'attendees' => $attendees, 
             'custom_fields' => $customFields, 
             'uk_ses' => $user_unit_kerja_ids,
             'lvl_ses' => session('level')
         ));
    }

    public function editRapat(Request $request)
    {
        $data = $request->data;
        $rapat = RapatModel::find($data['rapat']);

        

        //Cek dulu apakah dia barengan sama rapat lain (kalau bukan zoom)
        if($data['ruang_rapat'] != "Zoom Meeting Room Only"){
            $rapatoverlap = $this->cekOverlap($data);
            // Log::debug('EditRapat $data:', $data);
            // Log::debug('EditRapat $rapatoverlap:', $rapatoverlap->toArray());
                            
            //kalau ada, berarti kirim 409. yaitu ada konflik database.
            if ($rapatoverlap->count() > 0 && $rapatoverlap->first()->id != $rapat->id) {
                http_response_code(409);
                
                // echo json_encode(array('result' => 'error', 'namarapat' => $rapatoverlap->first()->nama, 'tanggalrapat' => $rapatoverlap->first()->tanggal_rapat_start));
                echo "Rapat anda bentrok dengan rapat ".$rapatoverlap->first()->nama." di tanggal ".  $rapatoverlap->first()->tanggal_rapat_start."<br><br>Hubungi Admin apabila ini sebuah kesalahan.";
                
                //force exit biar ga kekirim ke db
                exit();
            }
        }

        
         // Define field mapping: data_key => [db_field, display_label, formatter]
         $fieldMapping = [
             'nama_rapat' => ['db_field' => 'nama', 'label' => 'Nama Rapat', 'type' => 'text'],
             'topik_rapat' => ['db_field' => 'topik', 'label' => 'Topik Rapat', 'type' => 'text'],
             'unit_kerja' => ['db_field' => 'unit_kerja', 'label' => 'Unit Kerja', 'type' => 'unit_kerja'],
             'ruang_rapat' => ['db_field' => 'ruang_rapat', 'label' => 'Ruang Rapat', 'type' => 'text'],
             'jumlah_peserta' => ['db_field' => 'jumlah_peserta', 'label' => 'Jumlah Peserta', 'type' => 'text'],
             'mulai_rapat' => ['db_field' => 'waktu_mulai_rapat', 'label' => 'Waktu Mulai', 'type' => 'text'],
             'akhir_rapat' => ['db_field' => 'waktu_selesai_rapat', 'label' => 'Waktu Selesai', 'type' => 'text'],
             'tanggal_mulai' => ['db_field' => 'tanggal_rapat_start', 'label' => 'Tanggal Mulai', 'type' => 'date'],
             'tanggal_selesai' => ['db_field' => 'tanggal_rapat_end', 'label' => 'Tanggal Selesai', 'type' => 'date'],
             'is_use_zoom' => ['db_field' => 'use_zoom', 'label' => 'Sewa Zoom', 'type' => 'zoom'],
             'nomor_wa' => ['db_field' => 'nohp_pj', 'label' => 'No. WA PJ Rapat', 'type' => 'text'],
         ];
         
         // Helper function to format values for display
         $formatValue = function($value, $type) {
             if ($type === 'date') {
                 if (empty($value)) return '-';
                 try {
                     return date('j F Y', strtotime($value));
                 } catch (Exception $e) {
                     return $value;
                 }
             } elseif ($type === 'zoom') {
                 return ($value == '1' || $value === 1) ? 'Ya' : 'Tidak';
             } elseif ($type === 'unit_kerja') {
                 if (empty($value)) return '-';
                 $uk = UnitKerjaModel::find($value);
                 return $uk ? $uk->nama : $value;
             }
             return $value ?: '-';
         };
         
         // Detect only changed fields
         $changes = [];
         foreach ($fieldMapping as $dataKey => $config) {
             $oldValue = $rapat->{$config['db_field']};
             $newValue = $data[$dataKey] ?? null;
             
             // Compare values (handle null and type casting)
             $oldNormalized = ($config['type'] === 'zoom') ? (int)$oldValue : $oldValue;
             $newNormalized = ($config['type'] === 'zoom') ? (int)$newValue : $newValue;
             
             if ($oldNormalized != $newNormalized) {
                 $changes[] = [
                     'label' => $config['label'],
                     'old' => $formatValue($oldValue, $config['type']),
                     'new' => $formatValue($newValue, $config['type']),
                     'type' => $config['type']
                 ];
             }
         }
         
         // Send notification only if there are actual changes
         if (count($changes) > 0) {
             // Build message with only changed fields
             $changeList = '';
             foreach ($changes as $change) {
                 $changeList .= '%0A✏️ *' . $change['label'] . '*:';
                 $changeList .= '%0A   Sebelum: ' . $change['old'];
                 $changeList .= '%0A   Sesudah: ' . $change['new'];
                 $changeList .= '%0A';
             }
             
             // Notification to meeting creator
             $pembuat = UserModel::find($rapat->created_by);
             if ($pembuat) {
                 $messageheader = '*[Notifikasi MeetApp Kalsel]-Revisi* %0A %0A Hai, %0A Rapat *' . $rapat->nama . '* direvisi oleh *' . session('nama') . '* dengan perubahan:%0A';
                 $wapembuat = array(
                     'message' => urldecode($messageheader . $changeList),
                     'to' => $pembuat->no_hp
                 );
                 dispatch(new NotifWa($wapembuat));
             }
             
             // Notification to admin users
             $admin = NotifUmum::all();
             foreach ($admin as $adminUser) {
                 $messageheader2 = '*[Notifikasi MeetApp Kalsel]-Revisi* %0A %0A Hai ' . $adminUser->nama . ', %0A Ada rapat *' . $rapat->nama . '* direvisi oleh *' . session('nama') . '* dengan perubahan:%0A';
                 $waperubah = array(
                     'message' => urldecode($messageheader2 . $changeList),
                     'to' => $adminUser->no_wa
                 );
                 dispatch(new NotifWa($waperubah));
             }
         }
        $rapat->unit_kerja = $data['unit_kerja'];
        $rapat->nama = $data['nama_rapat'];
        $rapat->ruang_rapat = $data['ruang_rapat'];
        $rapat->jumlah_peserta = $data['jumlah_peserta'];
        $rapat->topik = $data['topik_rapat'];
        $rapat->waktu_mulai_rapat = $data['mulai_rapat'];
        $rapat->waktu_selesai_rapat = $data['akhir_rapat'];        
        // $rapat->use_zoom = $data['is_use_zoom'] == 'true' ? 1 : 0;                
        $rapat->use_zoom = $data['is_use_zoom'];                
        $rapat->nohp_pj = $data['is_use_zoom'] == '1' ? $data['nomor_wa'] : null;
        $rapat->save();
        
        // Sync attendees (delete old ones and add new ones)
        DB::table('rapat_user')->where('rapat_id', $rapat->id)->delete();
        if (isset($data['attendees']) && is_array($data['attendees'])) {
            foreach ($data['attendees'] as $attendeeId) {
                // Parse the composite ID (e.g., 'user-1' or 'unit_kerja-5')
                if (strpos($attendeeId, 'user-') === 0) {
                    $userId = str_replace('user-', '', $attendeeId);
                    DB::table('rapat_user')->insert([
                        'rapat_id' => $rapat->id,
                        'attendee_id' => $userId,
                        'attendee_type' => 'App\UserModel'
                    ]);
                } elseif (strpos($attendeeId, 'unit_kerja-') === 0) {
                    $unitKerjaId = str_replace('unit_kerja-', '', $attendeeId);
                    DB::table('rapat_user')->insert([
                        'rapat_id' => $rapat->id,
                        'attendee_id' => $unitKerjaId,
                        'attendee_type' => 'App\UnitKerjaModel'
                    ]);
                }
            }
        }
        
        // Sync custom fields (delete old ones and add new ones)
        RapatCustomField::where('rapat_id', $rapat->id)->delete();
        if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
            foreach ($data['custom_fields'] as $index => $field) {
                if (!empty($field['key']) && !empty($field['value'])) {
                    RapatCustomField::create([
                        'rapat_id' => $rapat->id,
                        'field_key' => $field['key'],
                        'field_value' => $field['value'],
                        'field_order' => $index
                    ]);
                }
            }
        }
        
        echo json_encode(array('result' => 'sukses'));
    }

    public function editTanggalRapatDrag(Request $request)
    {
        $data = $request->data;

        //Cek dulu apakah dia barengan sama rapat lain (kalau bukan zoom)
        if($data['ruang_rapat'] != "Zoom Meeting Room Only"){
            $rapatoverlap = $this->cekOverlap($data);
                            
            //kalau ada, berarti kirim 409. yaitu ada konflik database.
            if ($rapatoverlap->count() > 0) {
                http_response_code(409);
                // echo json_encode(array('result' => 'error', 'namarapat' => $rapatoverlap->first()->nama, 'tanggalrapat' => $rapatoverlap->first()->tanggal_rapat_start));
                echo "Rapat anda bentrok dengan rapat ".$rapatoverlap->first()->nama." di tanggal ".  $rapatoverlap->first()->tanggal_rapat_start."<br><br>Hubungi Admin apabila ini sebuah kesalahan.";
                //force exit biar ga kekirim ke db
                exit();
            }
        }

        $rapat = RapatModel::find($data['rapat']);
        $rapat->tanggal_rapat_start = $data['tanggal_rapat_start'];
        $rapat->tanggal_rapat_end = $data['tanggal_rapat_end'];
        $rapat->save();
        echo json_encode(array('result' => 'sukses'));
    }

    public function editTanggalRapatResize(Request $request)
    {
        $data = $request->data;

        //Cek dulu apakah dia barengan sama rapat lain (kalau bukan zoom)
        if($data['ruang_rapat'] != "Zoom Meeting Room Only"){
            $rapatoverlap = $this->cekOverlap($data);
                            
            //kalau ada, berarti kirim 409. yaitu ada konflik database.
            if ($rapatoverlap->count() > 0) {
                http_response_code(409);
                // echo json_encode(array('result' => 'error', 'namarapat' => $rapatoverlap->first()->nama, 'tanggalrapat' => $rapatoverlap->first()->tanggal_rapat_start));
                echo "Rapat anda bentrok dengan rapat ".$rapatoverlap->first()->nama." di tanggal ".  $rapatoverlap->first()->tanggal_rapat_start."<br><br>Hubungi Admin apabila ini sebuah kesalahan.";
                //force exit biar ga kekirim ke db
                exit();
            }
        }

        $rapat = RapatModel::find($data['rapat']);
        $rapat->tanggal_rapat_start = $data['tanggal_rapat_start'];
        $rapat->tanggal_rapat_end = $data['tanggal_rapat_end'];
        $rapat->save();
        echo json_encode(array('result' => 'sukses'));
    }

    public function hapusRapat(Request $request)
    {
        $rapat = RapatModel::find($request->rapat);
        $rapat->delete();
        echo json_encode(array('result' => 'sukses'));
    }

     public function getRapatAll(Request $request)
     {
         $uk_user = session('unit_kerja');
         $lv_user = session('level');
         $tahun = $request->input('tahun', date('Y'));
         
         $rapat = RapatModel::select(
                 'rapat.id',
                 'rapat.uid',
                 'rapat.nama',
                 'rapat.topik',
                 'rapat.tanggal_rapat_start',
                 'rapat.tanggal_rapat_end',
                 'rapat.waktu_mulai_rapat',
                 'rapat.waktu_selesai_rapat',
                 'rapat.use_zoom',
                 'rapat.is_notulensi',
                 'unit_kerja.nama as nama_unit_kerja'
             )
             ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')
             ->whereYear('rapat.tanggal_rapat_start', $tahun)
             ->where(function($query) use ($uk_user, $lv_user) {
                 if($lv_user != '2')
                 {
                     $query->where('unit_kerja', $uk_user);
                 }
             })
             ->get();
         
         echo json_encode($rapat);
     }

    public function getDetailZoom(Request $request)
    {
        $rapat = RapatModel::select('rapat.*', 'zoom.zoom_id', 'zoom.zoom_password', 'zoom.zoom_link', 'zoom.tanggal_zoom', 'zoom.id as zoom', 'zoom.host as host', 'users.nama as nama_host')
                ->leftJoin('zoom', 'zoom.rapat', '=', 'rapat.id')
                ->leftJoin('users', 'users.id', '=', 'zoom.host')
                ->where('rapat.id', $request->data)                
                ->get();
        if($rapat)
        {
            echo json_encode(array('result' => 'sukses', 'data' => $rapat, 'u_lv' => session('level')));
        } else
        {
            echo json_encode(array('result' => 'gagal'));
        }
    }

    public function uploadNotulensi(Request $request)
    {
        $file = $request->file('notulensi');
        // $result;
        if( strtolower( $file->getClientOriginalExtension() ) != 'pdf' && strtolower( $file->getClientOriginalExtension() ) != 'docx' && strtolower( $file->getClientOriginalExtension() ) != 'doc')
        {
            $result = ['status' => 'gagal', 'message' => 'Tipe file yang boleh diupload hanya : .pdf, .docx, dan .doc'];            
        } else 
        {
            $rapat = RapatModel::select('rapat.id as id_rapat','rapat.nama', 'unit_kerja.nama as nama_unit_kerja')->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')->where('rapat.id', $request->rapat)->first();            
            $ts = \Carbon\Carbon::now()->timestamp;
            $notulensi = $rapat->nama_unit_kerja.'_'.substr($rapat->nama, 0, 30).(strlen($rapat->nama) > 30 ? '...' : '').'_'.(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'@'.$ts.'_r'.$request->rapat.'.'. strtolower($file->getClientOriginalExtension());
            $file->move(public_path('notulensi'), $notulensi);

            $upload_notulensi = new NotulensiModel;
            $upload_notulensi->rapat = $request->rapat;
            $upload_notulensi->nama_file = $file->getClientOriginalName();
            $upload_notulensi->created_at = $ts;
            $upload_notulensi->created_by = session('user_id');
            $upload_notulensi->save();

            $rapat_temp = RapatModel::find($request->rapat);
            $rapat_temp->is_notulensi = 1;
            $rapat_temp->save();

            $result = ['status' => 'sukses', 'message' => 'Berhasil upload notulensi'];
        }
        echo json_encode($result);
    }

    public function hapusNotulensi(Request $request)
    {
        $rapat = RapatModel::find($request->rapat);
        $rapat->is_notulensi = 0;
        $rapat->save();

        $notulensi = NotulensiModel::where('rapat', $request->rapat)->delete();

        $mask_final = public_path('notulensi').'/*_r'.$request->rapat.'.*';
        array_map('unlink', glob($mask_final));   

        echo json_encode(array('result' => 'sukses'));
    }

    public function downloadNotulensi(Request $request)
    {
        $rapat = $request->get('rapat');
        if($rapat)
        {
            $notulensi = NotulensiModel::where('rapat', $rapat)->orderBy('id', 'desc')->first();
            $data_rapat = RapatModel::select('rapat.*', 'unit_kerja.nama as nama_unit_kerja')->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')->where('rapat.id', $rapat)->first();
            if($notulensi && (session('level') == '2' || session('unit_kerja') == $data_rapat->unit_kerja  )) // yang bisa download cuman admin atau user dengan satu unit kerja yang sama dengan rapatnya ...
            {                            
                $mask_final = public_path('notulensi').'/*_r'.$rapat.'.*';
                $files = glob($mask_final);
                $zip_file = public_path('notulensi/download/('.$data_rapat->nama_unit_kerja.') Notulen_'.$data_rapat->nama.'@'. \Carbon\Carbon::now()->timestamp.'.zip');
                Zipper::make($zip_file)->add($files)->close();
                return response()->download($zip_file)->deleteFileAfterSend(true); // download file to user abis itu didelete lagi file nya dari sistem biar ga banyak file ...
            } else 
            {
                return view('error.404');
            }
        } else 
        {
            return view('error.404');
        }
    }

    public function getDetailRapat(Request $request)
    {
        $rapat = RapatModel::find($request->data);
        $zoom = ZoomModel::select('zoom.*', 'users.nama as nama_host')->leftJoin('users', 'users.id', '=', 'zoom.host')->where('rapat', $request->data)->get();
        echo json_encode(array('rapat' => $rapat, 'zoom' => $zoom));

    }

    /**
     * Generate a unique 6-character alphabetic UID
     *
     * @return string
     */
    private function generateUniqueUid()
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $uid = '';
            for ($i = 0; $i < 6; $i++) {
                $uid .= $alphabet[rand(0, strlen($alphabet) - 1)];
            }
            $exists = DB::table('rapat')->where('uid', $uid)->exists();
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        if ($attempts >= $maxAttempts) {
            // Fallback: use shuffled approach if too many collisions
            $uid = substr(str_shuffle($alphabet), 0, 6);
            $counter = 1;
            while (DB::table('rapat')->where('uid', $uid)->exists()) {
                $uid = substr(str_shuffle($alphabet), 0, 5) . substr($counter, -1);
                $counter++;
            }
        }

        return $uid;
    }

    /**
     * Show meeting details by UID (public access)
     *
     * @param string $uid
     * @return \Illuminate\View\View
     */
    public function showMeetingByUid($uid)
    {
        // Find meeting by UID
        $rapat = RapatModel::select('rapat.*', 'unit_kerja.nama as nama_unit_kerja', 'unit_kerja.singkatan as singkatan_unit_kerja')
            ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')
            ->where('rapat.uid', $uid)
            ->first();

        if (!$rapat) {
            abort(404, 'Meeting not found');
        }

        // Get creator info
        $creator = null;
        if ($rapat->created_by) {
            $creator = UserModel::find($rapat->created_by);
        }

        // Get attendees
        $attendees = $rapat->getAllAttendees();

        // Get zoom details if exists
        $zoomDetails = null;
        if ($rapat->use_zoom == 1) {
            $zoomDetails = ZoomModel::select('zoom.*', 'users.nama as nama_host')
                ->leftJoin('users', 'users.id', '=', 'zoom.host')
                ->where('zoom.rapat', $rapat->id)
                ->get();
        }

        // Check if documentation exists
        // $hasDocumentation = $rapat->is_notulensi == 1;
        // $notulensi = null;
        // if ($hasDocumentation) {
        //     $notulensi = NotulensiModel::where('rapat', $rapat->id)->orderBy('id', 'desc')->first();
        // }

        // return view('pages.meeting-detail', compact('rapat', 'creator', 'attendees', 'zoomDetails', 'hasDocumentation', 'notulensi'));
        $notulensiFiles = [];
        $notulensi = NotulensiModel::where('rapat', $rapat->id)->orderBy('id', 'desc')->first();
        if ($notulensi) {
            $mask_final = public_path('notulensi').'/*_r'.$rapat->id.'.*';
            $files = glob($mask_final);
            foreach ($files as $file) {
                $notulensiFiles[] = [
                    'name' => basename($file),
                    'path' => 'notulensi/download/' . basename($file),
                    'size' => filesize($file),
                    'extension' => pathinfo($file, PATHINFO_EXTENSION),
                    'created_at' => filemtime($file)
                ];
            }
        }
        
        // Get custom fields
        $customFields = RapatCustomField::where('rapat_id', $rapat->id)
                        ->orderBy('field_order', 'asc')
                        ->get();
        
        return view('pages.meeting-detail', compact('rapat', 'creator', 'attendees', 'zoomDetails', 'notulensiFiles', 'customFields'));
    }

    /**
     * Public download documentation by UID
     *
     * @param string $uid
     * @return \Illuminate\Http\Response
     */
    public function downloadNotulensiByUid($uid)
    {
        $rapat = RapatModel::where('uid', $uid)->first();
        
        if (!$rapat) {
            abort(404, 'Meeting not found');
        }

        $notulensi = NotulensiModel::where('rapat', $rapat->id)->orderBy('id', 'desc')->first();
        
        if (!$notulensi) {
            abort(404, 'Documentation not found');
        }

        $mask_final = public_path('notulensi').'/*_r'.$rapat->id.'.*';
        $files = glob($mask_final);

        if (empty($files)) {
            abort(404, 'Documentation files not found');
        }

        // Build a shorter, user-friendly zip filename based on the original uploaded filename.
        // This does NOT rename the files inside the zip; it only sets the name of the zip file itself.
        $originalName = $notulensi->nama_file; // e.g. Laporan_Rapat_Bulanan.pdf
        $baseName = $rapat->nama;

        // Sanitize and shorten base name
        $baseName = preg_replace('/[^A-Za-z0-9 _\-]/', '', $baseName);
        if ($baseName === '' || $baseName === null) {
            $baseName = 'Dokumentasi_Rapat';
        }
        // Limit base name length to avoid filesystem issues
        if (strlen($baseName) > 50) {
            $baseName = substr($baseName, 0, 50);
        }

        //get hour and minute
        $timestamp = date('H-i');
        $zipFileName = $baseName . '_' . $timestamp . '.zip';
        $zip_file = public_path('notulensi/download/' . $zipFileName);

        // Ensure download directory exists
        if (!file_exists(public_path('notulensi/download'))) {
            mkdir(public_path('notulensi/download'), 0755, true);
        }

        Zipper::make($zip_file)->add($files)->close();
        return response()->download($zip_file)->deleteFileAfterSend(true);
    }
    public function downloadNotulensiFile($filename)
    {
        // Security check: Only allow filenames that match the pattern
        // if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        //     abort(400, 'Invalid filename');
        // }
        if ($filename == '') {
            abort(404, 'File not found');
        }
        $filepath = public_path('notulensi/' . $filename);
        
        if (!file_exists($filepath)) {
            abort(404, 'File not found');
        }
        // die();
        // return "<a href='".$filepath."' download>Unduh File</a>";
        return response()->download($filepath, $filename);
    }
    public function saveZoomRapat(Request $request)
    {
        $data = $request->data;
        $zoom = ZoomModel::updateOrCreate(
            ['rapat' => $data['rapat'], 'tanggal_zoom' => $data['tanggal']],
            ['zoom_id' => $data['zoom_id'], 'zoom_password' => $data['zoom_pw'], 'zoom_link' => $data['zoom_link'], 'host' => $data['host']]
        );
        echo json_encode(array('result' => 'sukses', 'zoom_fn' => $zoom->id));
    }

    public function getZoomRinc(Request $request)
    {
        $zoom = ZoomModel::find($request->data);
        echo json_encode(array('result' => $zoom, 'status' => (session('level') == '2' ? 'allowed' : 'forbidden') ));
    }

    public function saveEditZoom(Request $request)
    {
        $data = $request->data;
        $zoom = ZoomModel::find($data['zoom']);
        if($data['stat'] == 'z-id')
        {
            $zoom->zoom_id = $data['value'];
        } else if($data['stat'] == 'z-pw')
        {
            $zoom->zoom_password = $data['value'];
        } else if($data['stat'] == 'z-link')
        {
            $zoom->zoom_link = $data['value'];
        }
        $zoom->save();
        echo json_encode(array('result' => 'sukses'));
        
    }

    public function sendNotifZoom(Request $request)
    {
        $zoom = ZoomModel::find($request->zoom);
        $rapat = RapatModel::find($zoom->rapat);
        $host = DB::table('users')->where('id', $zoom->host)->first();
        $unit_kerja_temp = UnitKerjaModel::find($rapat->unit_kerja);
        $message = '*[Notifikasi MeetApp Kalsel]*  %0A  %0A  Hai, %0A  Permintaan Zoom Meeting anda pada tanggal '.date("j F Y", strtotime($rapat->created_at)).' sudah disetujui.';    
        $message .= ' Berikut merupakan rincian permintaan zoom meeting anda: %0A ';
        $message .= ' %0A  Unit Kerja: '.$unit_kerja_temp->nama;
        $message .= ' %0A  Nama rapat: '.$rapat->nama;
        $message .= ' %0A  Topik rapat: '.$rapat->topik;
        $date_temp = (new \DateTime($rapat->tanggal_rapat_start))->diff(new \DateTime($rapat->tanggal_rapat_end))->format('%a') > 0 ? date('j F Y', strtotime($rapat->tanggal_rapat_start)).' - '.date('j F Y', strtotime($rapat->tanggal_rapat_end)) : date('j F Y', strtotime($rapat->tanggal_rapat_start));
        $message .= ' %0A  Tanggal Rapat: '.$date_temp;
        $message .= ' %0A  Jam Rapat: '.$rapat->waktu_mulai_rapat.' - '.$rapat->waktu_selesai_rapat;
        $message .= ' %0A  %0A  *Zoom ID: '.$zoom->zoom_id.'*';
        $message .= ' %0A  *Zoom Password: '.$zoom->zoom_password.'*';
        $message .= ' %0A  *Link Zoom: '.$zoom->zoom_link.'*';
        $message .= ' %0A  *Tanggal Zoom: '.date("j F Y", strtotime($zoom->tanggal_zoom)).'*';
        $message .= ' %0A  *PJ TI: '.$host->nama.'*';

        $wa = array(
            'message' => urldecode($message),
            'to' => $request->data
        );
        dispatch(new NotifWa($wa));  
        
        // kirim notif ke Host Zoom Meeting:
        if($zoom->host != null || $zoom->host != '' || $zoom->host != 0) // kalo host zoomnya sudah di set ...
        {
            $host = DB::table('users')->where('id', $zoom->host)->first();
            $message = ' ⚠️ *[Notifikasi MeetApp Kalsel]-Assign PJ* ⚠️  %0A  %0A Halo '.$host->nama.', %0A Anda ditunjuk sebagai Penanggung Jawab pada kegiatan zoom meeting berikut:';    
            $message .= ' %0A ';
            $message .= ' %0A Unit Kerja: '.$unit_kerja_temp->nama;
            $message .= ' %0A Nama rapat: '.$rapat->nama;
            $message .= ' %0A Topik rapat: '.$rapat->topik;
            $date_temp = (new \DateTime($rapat->tanggal_rapat_start))->diff(new \DateTime($rapat->tanggal_rapat_end))->format('%a') > 0 ? date('j F Y', strtotime($rapat->tanggal_rapat_start)).' - '.date('j F Y', strtotime($rapat->tanggal_rapat_end)) : date('j F Y', strtotime($rapat->tanggal_rapat_start));
            $message .= ' %0A Tanggal Rapat: '.$date_temp;
            $message .= ' %0A Jam Rapat: '.$rapat->waktu_mulai_rapat.' - '.$rapat->waktu_selesai_rapat;
            $message .= ' %0A  %0A *Zoom ID: '.$zoom->zoom_id.'*';
            $message .= ' %0A *Zoom Password: '.$zoom->zoom_password.'*';
            $message .= ' %0A *Link Zoom: '.$zoom->zoom_link.'*';
            $message .= ' %0A *Tanggal Zoom: '.date("j F Y", strtotime($zoom->tanggal_zoom)).'*';
            $message .= ' %0A  %0A ';
            $message .= 'Jangan lupa untuk menyalakan zoom meeting sesuai dengan jadwal tersebut. Terima kasih banyak atas bantuannya. Cheers :)';

            $wa = array(
                'message' => urldecode($message),
                'to' => $host->no_hp
            );
            dispatch(new NotifWa($wa));
        }

    }

    public function getPJZoom(Request $request)
    {
        $zoom = ZoomModel::find($request->data);
        $rapat = RapatModel::find($zoom->rapat);
        echo json_encode($rapat);
    }

    public function getCalonHost(Request $request)
    {
        $data = $request->data;
        $host = DB::table('users')->where('unit_kerja', '1')->get(); // get semua user yg unit kerjanya bidang ipds
        echo json_encode(array('result' => $host));
    }

    public function getZoomHost(Request $request)
    {
        $data = $request->data;
        $host = DB::table('users')->where('unit_kerja', '1')->get(); // get semua user yg unit kerjanya bidang ipds
        $zoom = DB::table('zoom')->where('id', $data)->first();
        echo json_encode(array('host' => $host, 'zoom' => $zoom ));
    }

    public function editHost(Request $request)
    {
        $data = $request->data;
        DB::table('zoom')->where('id', $data['zoom'])->update(['host' => $data['value']]);
        echo json_encode(array('result' => 'sukses'));
    }

    public function reminderZoomMessage_PJ($data)
    {
        // reminder untuk pj zoom meeting:
        $message = '*[Notifikasi MeetApp Kalsel] - Reminder Zoom Meeting*';
        $message .= ' %0A  %0A ';
        $message .= 'Hai,';
        $message .= ' %0A ';
        $message .= 'Anda memiliki zoom meeting yang akan dilaksanakan hari ini dengan rincian sebagai berikut:';
        $message .= ' %0A  %0A ';
        $message .= 'Nama rapat: '.$data->nama;
        $message .= ' %0A ';
        $message .= 'Topik rapat: '.$data->topik;
        $message .= ' %0A ';
        $message .= 'Unit Kerja: '.$data->nama_unit_kerja;
        $message .= ' %0A  %0A ';

        $date_temp = (new \DateTime($data->tanggal_rapat_start))->diff(new \DateTime($data->tanggal_rapat_end))->format('%a') > 0 ? date('j F Y', strtotime($data->tanggal_rapat_start)).' - '.date('j F Y', strtotime($data->tanggal_rapat_end)) : date('j F Y', strtotime($data->tanggal_rapat_start));
        $message .= 'Tanggal Rapat: '.$date_temp;
        $message .= ' %0A ';
        $message .= 'Jam Rapat: '.$data->waktu_mulai_rapat.' - '.$data->waktu_selesai_rapat;
        $message .= ' %0A  %0A ';

        $message .= '*Zoom ID: '.$data->zoom_id.'*';
        $message .= ' %0A ';
        $message .= '*Zoom Password: '.$data->zoom_password.'*';
        $message .= ' %0A ';
        $message .= '*Link Zoom: '.$data->zoom_link.'*';
        $message .= ' %0A  %0A ';

        $message .= '*Host Zoom Meeting: '.$data->nama_host.'*';
        $message .= ' %0A  %0A ';

        $message .= 'Silahkan hubungi Host Zoom Meeting tersebut jika terdapat kendala ataupun terdapat perubahan jadwal pelaksanaan zoom meeting. Terima kasih :)';

        $wa = array(
            'message' => urldecode($message),
            'to' => $data->nohp_pj
        );
        dispatch(new NotifWa($wa));
    }


    // function ini deprecated, karena kirimnya per pj. Baiknya sekaligus semua tau aja
    public function reminderZoomMessage_Host($zoom_today)
    {
        // reminder untuk host zoom:
        
        $message = '*[Notifikasi MeetApp Kalsel] - Reminder Host Zoom Meeting*';
        $message .= ' %0A  %0A ';
        $message .= 'Halo *'.$zoom_today[0]->nama_host.'*,';
        $message .= ' %0A ';
        $message .= 'Anda menjadi host pada '.count($zoom_today).' zoom meeting yang akan dilaksanakan hari ini dengan rincian sebagai berikut:';
        $message .= ' %0A  %0A ';

        foreach($zoom_today as $key => $data)
        {
            $this->reminderZoomMessage_PJ($data); // send message to PJ Zoom Meeting
            $message .= '*'.($key+1).'. '.$data->nama.'*';
            $message .= ' %0A ';
            $message .= 'Topik rapat: '.$data->topik;
            $message .= ' %0A ';
            $message .= 'Unit Kerja: '.$data->nama_unit_kerja;
            $message .= ' %0A ';
            $message .= 'No HP Penganggung Jawab Rapat: '.$data->nohp_pj;
            $message .= ' %0A ';

            $date_temp = (new \DateTime($data->tanggal_rapat_start))->diff(new \DateTime($data->tanggal_rapat_end))->format('%a') > 0 ? date('j F Y', strtotime($data->tanggal_rapat_start)).' - '.date('j F Y', strtotime($data->tanggal_rapat_end)) : date('j F Y', strtotime($data->tanggal_rapat_start));
            $message .= 'Tanggal Rapat: '.$date_temp;
            $message .= ' %0A ';
            $message .= 'Jam Rapat: '.$data->waktu_mulai_rapat.' - '.$data->waktu_selesai_rapat;
            $message .= ' %0A ';

            $message .= 'Zoom ID: *'.$data->zoom_id.'*';
            $message .= ' %0A ';
            $message .= 'Zoom Password: *'.$data->zoom_password.'*';
            $message .= ' %0A ';
            $message .= 'Link Zoom: *'.$data->zoom_link.'*';
            $message .= ' %0A  %0A ';
        }

        $message .= 'Jangan lupa untuk menyalakan zoom meeting sesuai dengan jadwal tersebut. Terima kasih banyak atas bantuannya. Cheers :)';

        $wa = array(
            'message' => urldecode($message),
            'to' => $data->no_hp_host
        );
        dispatch(new NotifWa($wa));
    }


    public function reminderRuangRapat_Umum($rooms)
    {
        // reminder untuk umum

        $admin = NotifUmum::all();            
        foreach($admin as $admin){

            $message = '*[Notifikasi MeetApp Kalsel] - Reminder Ruang Rapat*';
            $message .= ' %0A  %0A ';
            $message .= 'Halo '.$admin->nama.'!';
            $message .= ' %0A ';
            $message .= 'Akan ada '.count($rooms).' rapat dengan rincian sebagai berikut:';
            $message .= ' %0A  %0A ';

            foreach($rooms as $key => $data)
            {
                $message .= '*'.($key+1).'. '.$data->nama.'*';
                $message .= ' %0A ';
                $message .= 'Topik rapat: '.$data->topik;
                $message .= ' %0A ';
                $message .= 'Unit Kerja: '.$data->nama_unit_kerja;
                $message .= ' %0A ';
                $message .= 'Ruang Rapat: '.$data->ruang_rapat;
                $message .= ' %0A ';
                $date_temp = (new \DateTime($data->tanggal_rapat_start))->diff(new \DateTime($data->tanggal_rapat_end))->format('%a') > 0 ? date('j F Y', strtotime($data->tanggal_rapat_start)).' - '.date('j F Y', strtotime($data->tanggal_rapat_end)) : date('j F Y', strtotime($data->tanggal_rapat_start));
                $message .= 'Tanggal Rapat: '.$date_temp;
                $message .= ' %0A ';
                $message .= 'Jam Rapat: '.$data->waktu_mulai_rapat.' - '.$data->waktu_selesai_rapat;
                $message .= ' %0A ';
                $message .= 'Jumlah Peserta Rapat: '.$data->jumlah_peserta;
                $message .= ' %0A ';
                $message .= ' %0A ';
            }
            $message .= ' %0A ';
            $message .= 'Jangan lupa untuk mempersiapkan ruangan sesuai dengan jadwal tersebut. Terima kasih banyak atas bantuannya. Cheers :)';

            $wa = array(
                'message' => urldecode($message),
                'to' => $admin->no_wa
                //nomor bagian umum
            );
            dispatch(new NotifWa($wa));
        }
    }

    public static function getEloquentSqlWithBindings($query)
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            $binding = addslashes($binding);
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }

    public function reminderRuangRapat(Request $request){
        date_default_timezone_set('Asia/Makassar');
        $now = new \DateTime();
        $oneHourFromNow = new \DateTime();
        $oneHourFromNow->modify('+75 minutes');

        $meetings = DB::table('rapat')
        ->select('rapat.*', 'unit_kerja.nama as nama_unit_kerja')
        ->whereDate('tanggal_rapat_start', '=', date('Y-m-d'))
        ->where('waktu_mulai_rapat', '>=', $now->format('H:i'))
        ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')
        ->where('waktu_mulai_rapat', '<=', $oneHourFromNow->format('H:i'))
        ->where('ruang_rapat', '!=', 'Zoom Meeting Room Only')
        ->get();

        if(count($meetings))$this->reminderRuangRapat_Umum($meetings);
        
        // var_dump($this->getEloquentSqlWithBindings($meetings));

        //catat setiap kali jalan
        $logs = new ScheduleLogModel;
        $logs->schedule_name='reminderRuangRapat';
        $logs->content=json_encode($meetings);
        $logs->save();
        
    }

    // function ini deprecated, karena kirimnya per pj. Baiknya sekaligus semua tau aja
    public function reminderHostZoom(Request $request)
    {
        date_default_timezone_set('Asia/Makassar');
        $today = date('Y-m-d');

        /*
        Query:
            #deskripsi: get daftar host zoom per-hari ini (today)
            Select DISTINCT host from zoom where zoom.tanggal_zoom = $today
        */

        $host_today = DB::table('zoom')->select(DB::raw('DISTINCT host'))->where('tanggal_zoom', $today)->get();

        foreach($host_today as $hosts)
        {
            $host = $hosts->host;
            /*
            Query:
                # deskripsi: get semua zoom hari ini untuk setiap host

                SELECT zoom.zoom_id, zoom.zoom_password, zoom.zoom_link, zoom.tanggal_zoom, rapat.*, 
                unit_kerja.nama as nama_unit_kerja, users.nama as nama_host, users.no_hp as no_hp_host FROM `zoom`
                left join rapat on rapat.id = zoom.rapat
                left join unit_kerja on unit_kerja.id = rapat.unit_kerja
                left join users on users.id = zoom.host
                where zoom.host = $list_host_today and zoom.tanggal_zoom = $today
                order by str_to_date(rapat.waktu_mulai_rapat,'%k:%i') ASC
            */

            $zoom_today = DB::table('zoom')
                    ->select(
                        'zoom.zoom_id', 'zoom.zoom_password', 'zoom.zoom_link', 'zoom.tanggal_zoom',
                        'rapat.*', 'unit_kerja.nama as nama_unit_kerja', 
                        'users.nama as nama_host', 'users.no_hp as no_hp_host', 'users.id as id_host'
                    )
                    ->leftJoin('rapat', 'rapat.id', '=', 'zoom.rapat')
                    ->leftJoin('users', 'users.id', '=', 'zoom.host')
                    ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')
                    ->where(array(
                        'zoom.tanggal_zoom' => $today,
                        'zoom.host' => $host
                    ))
                    ->orderBy(DB::raw("str_to_date(rapat.waktu_mulai_rapat,'%k:%i')"), 'ASC')
                    ->get(); // get semua zoom today untuk setiap host
            
            $this->reminderZoomMessage_Host($zoom_today); // kirim reminder ke Host Meeting
            $logs = new ScheduleLogModel;
            $logs->schedule_name='reminderHostZoom';
            $logs->content = json_encode($zoom_today); 
            $logs->save();
        }
        
        echo "sukses %0A  %0A <br>";
        // print_r($zoom_today);
        echo "<br>sukses %0A  %0A <br>";
        // print_r($logs);
    }

    public function reminderHostZoomAll(Request $request)
    {
        date_default_timezone_set('Asia/Makassar');
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime($today . ' +1 day'));
        // $today = $tomorrow;
        $zoom_today = DB::table('zoom')
                    ->select(
                        'zoom.zoom_id', 'zoom.zoom_password', 'zoom.zoom_link', 'zoom.tanggal_zoom',
                        'rapat.*', 'unit_kerja.nama as nama_unit_kerja', 
                        'users.nama as nama_host', 'users.no_hp as no_hp_host', 'users.id as id_host', 'sm.nama as nama_sm', 'sm.no_hp as no_hp_sm'
                    )
                    ->leftJoin('rapat', 'rapat.id', '=', 'zoom.rapat')
                    ->leftJoin('users', 'users.id', '=', 'zoom.host')
                    ->leftJoin('users as sm', 'sm.id', '=', 'rapat.created_by')
                    ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')
                    ->where(array(
                        'zoom.tanggal_zoom' => $today,
                    ))
                    ->orderBy(DB::raw("str_to_date(rapat.waktu_mulai_rapat,'%k:%i')"), 'ASC')
                    ->get(); // get semua zoom today 

        $this->reminderZoomMessage_AllHost($zoom_today); // kirim reminder ke Host Meeting
        $logs = new ScheduleLogModel;
        $logs->schedule_name='reminderHostZoomAll';
        $logs->content = json_encode($zoom_today); 
        $logs->save();
    }

    public function reminderZoomMessage_AllHost($rooms)
    {
        // reminder untuk umum

        $admin = NotifAdmin::all(); 
        if (count($rooms)<1) {
            return "Tidak ada rapat hari ini";
        }           
        foreach($admin as $admin){

            $message = '🌤🌤 *[Notifikasi MeetApp Kalsel] - Reminder Host Zoom Meeting* 🌤🌤';
            $message .= ' %0A  %0A ';
            $message .= 'Halo '.$admin->nama.'!';
            $message .= ' %0A ';
            $message .= 'Akan ada '.count($rooms).' rapat virtual dengan rincian sebagai berikut:';
            $message .= ' %0A  %0A ';

            foreach($rooms as $key => $data)
            {
                $message .= '*'.($key+1).'. '.$data->nama.' ('.$data->waktu_mulai_rapat.' - '.$data->waktu_selesai_rapat.')*';
                // $message .= ' %0A ';
                // $message .= 'Topik rapat: '.$data->topik;
                $message .= ' %0A ';
                $message .= 'Ruang Rapat: '.$data->ruang_rapat;
                $message .= ' %0A ';
                $message .= 'Pembuat: '.$data->nama_sm.' ('.$data->no_hp_sm.')';
                $message .= ' %0A ';
                $message .= 'PJ TI: '.$data->nama_host.' ('.$data->no_hp_host.')';
                $message .= ' %0A ';
                $message .= 'Link Zoom: '.$data->zoom_link;
                $message .= ' %0A ';
                $message .= ' %0A ';
            }
            $message .= ' %0A ';
            $message .= 'Saling mengingatkan ya! Cheers :)';

            $wa = array(
                'message' => urldecode($message),
                'to' => $admin->no_wa
                //nomor bagian ipds
            );
            
            dispatch(new NotifWa($wa)); 
            // break();
            sleep(1);
        }
        echo "<br>sukses %0A  %0A <br>";
    }

    public function tesDispatch()
    {
        $wa = array(
            'message' => 'Dispatch Test',
            'to' => '082113767398'
        );
        log::error($wa);
        dispatch(new NotifWa($wa));
    }

    //Kodingan untuk menerima notif dari PRTG (mce-jaringan.bpskalsel.com) dan ngirim ke orang orang yg didalam post nya.
    public function notifPrtg(Request $request)
    {
        if (!$request->has('usernames') || !$request->has('message')) {
            echo 'Parameter missing';
            log::error('param not found');
            log::error($request->all());
            return response('Parameter missing', 500);
        }
        $usernames = $request->input('usernames');
        $message = $request->input('message');
        foreach (explode(',', $usernames) as $username)
        {
            $usernya = DB::table('users')->where('username', $username)->first();
            $wa = array(
                'message' => $message,
                'to' => $usernya->no_hp
            );
            // log::error($wa);

            dispatch(new NotifWa($wa));
            
        }
        $res = array(
            'result' => 'false',
            'message' => $wa['message'],
            'to' => $usernames
        );
        echo json_encode($res);

    }

    use DispatchesJobs;
    public function quickNotif(Request $request)
    {   
        if (!$request->has('usernames') || !$request->has('message')) {
            // echo 'Parameter missing';
            log::error('param not found');
            log::error($request->all());
            return response('Parameter missing', 500);
        }
        $usernames = $request->input('usernames');
        $usernames = str_replace(' ', '', $request->input('usernames'));

        $message = $request->input('message');
        $res = array();
        $errorcount = 0;
        foreach (explode(',', $usernames) as $username)
        {
            $usernya = DB::table('users')->where('username', $username)->first();
            if(empty($usernya)){
                $errorcount++;
                $entity = array(
                    'result' => 'false',
                    'to' => $username,
                    'message' => 'User not found'
                );
                array_push($res, json_encode($entity));
            } else {
                $wa = array(
                    'message' => $message,
                    'to' => $usernya->no_hp
                );
                $response=$this->dispatchNow(new NotifWa($wa));
                $response = json_decode($response);
            }
            
            if($response->result=='false'){
                $errorcount++;
                $entity = array(
                    'result' => 'false',
                    'to' => $usernya->no_hp,
                    'message' => $response->message
                );
                array_push($res, json_encode($entity));
            } else {
                $entity = array(
                    'result' => 'true',
                    'to' => $usernya->no_hp,
                    'message' => $response->message
                );
                array_push($res, json_encode($entity));
            }
        }
        if($errorcount>0){
            $result = array(
                'result' => 'false',
                'message' => $res
            );
            log::info($result);
            echo json_encode($result);
        } else {
            $result = array(
                'result' => 'true',
                'message' => "Sukses"
            );
            echo json_encode($result);
        }
    }
  

}
