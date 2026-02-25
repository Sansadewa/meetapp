<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\UserModel;
use App\ScheduleLogModel;
use App\Jobs\AgendaHarianEmail;

class KirimAgendaHarian extends Command
{
    protected $signature = 'agenda:harian';
    protected $description = 'Send daily meeting agenda email to all users';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        date_default_timezone_set('Asia/Makassar');
        $today = date('Y-m-d');
        $totalUsers = 0;
        $totalDispatched = 0;

        // Get all active users
        $users = UserModel::where('is_active', 1)->get();

        foreach ($users as $user) {
            $totalUsers++;

            // Get user's unit IDs for polymorphic attendee matching
            $userUnitIds = DB::table('unit_kerja_user')
                ->where('user_model_id', $user->id)
                ->pluck('unit_kerja_model_id')
                ->toArray();

            // Query meetings for today where user is attendee
            // Either directly invited or via unit
            $meetings = DB::table('rapat')
                ->select(
                    'rapat.*',
                    'unit_kerja.nama as nama_unit_kerja',
                    'zoom.zoom_id',
                    'zoom.zoom_password',
                    'zoom.zoom_link'
                )
                ->whereDate('rapat.tanggal_rapat_start', '=', $today)
                ->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'rapat.unit_kerja')
                ->leftJoin('zoom', 'zoom.rapat', '=', 'rapat.id')
                ->where(function ($query) use ($user, $userUnitIds) {
                    // Direct invitation
                    $query->whereExists(function ($subquery) use ($user) {
                        $subquery->select(DB::raw(1))
                            ->from('rapat_user')
                            ->whereRaw('rapat_user.rapat_id = rapat.id')
                            ->where('rapat_user.attendee_type', 'App\UserModel')
                            ->where('rapat_user.attendee_id', $user->id);
                    })
                    // OR via unit invitation
                    ->orWhere(function ($query2) use ($userUnitIds) {
                        if (count($userUnitIds) > 0) {
                            $query2->whereExists(function ($subquery) use ($userUnitIds) {
                                $subquery->select(DB::raw(1))
                                    ->from('rapat_user')
                                    ->whereRaw('rapat_user.rapat_id = rapat.id')
                                    ->where('rapat_user.attendee_type', 'App\UnitKerjaModel')
                                    ->whereIn('rapat_user.attendee_id', $userUnitIds);
                            });
                        }
                    });
                })
                ->orderBy(DB::raw("STR_TO_DATE(rapat.waktu_mulai_rapat, '%k:%i')"), 'ASC')
                ->get();

            // Only dispatch if user has meetings
            if (count($meetings) > 0) {
                // Generate email from username@bps.go.id
                $user->email = $user->username . '@bps.go.id';
                dispatch(new AgendaHarianEmail($user, $meetings));
                $totalDispatched++;
            }
        }

        // Log to schedule_log
        $log = new ScheduleLogModel();
        $log->schedule_name = 'agendaHarian';
        $log->content = json_encode([
            'total_users' => $totalUsers,
            'total_dispatched' => $totalDispatched,
            'run_date' => $today,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        $log->save();

        $this->info("Agenda emails dispatched to {$totalDispatched} users (out of {$totalUsers} total active users)");
    }
}
