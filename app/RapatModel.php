<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RapatModel extends Model
{
    protected $table = 'rapat';
    public $timestamps = false;
    
    // Get all attendees (both users and unit_kerja)
    public function getAllAttendees()
    {
        $attendees = DB::table('rapat_user')
            ->where('rapat_id', $this->id)
            ->get();
        
        $result = array();
        foreach ($attendees as $attendee) {
            if ($attendee->attendee_type == 'App\UserModel') {
                $user = UserModel::find($attendee->attendee_id);
                if ($user) {
                    $result[] = array(
                        'id' => 'user-' . $user->id,
                        'text' => $user->nama,
                        'type' => 'user'
                    );
                }
            } elseif ($attendee->attendee_type == 'App\UnitKerjaModel') {
                $unitKerja = UnitKerjaModel::find($attendee->attendee_id);
                if ($unitKerja) {
                    $result[] = array(
                        'id' => 'unit_kerja-' . $unitKerja->id,
                        'text' => $unitKerja->nama,
                        'type' => 'unit_kerja'
                    );
                }
            }
        }
        return $result;
    }
    
    /**
     * Relationship to custom fields
     */
    public function customFields()
    {
        return $this->hasMany('App\RapatCustomField', 'rapat_id', 'id')
                    ->orderBy('field_order', 'asc');
    }
    
    protected static function boot()
    {
        parent::boot();

        //Saves Original Data Before Delete
        static::deleting(function ($model) {
            $data = $model->toArray();
            $data['original_id'] = $model->id;
            $data['deleted_by'] = session('user_id');
            unset($data['id']); // Remove the original id to avoid conflicts in deleted_rapat
            DB::table('rapatlog')->insert($data);
        });

        //Saves Original Data Before Update
        static::updating(function ($model) {
            $data = $model->toArray();
            $data['original_id'] = $model->id;
            $data['updated_by'] = session('user_id');
            unset($data['id']); // Remove the original id to avoid conflicts in deleted_rapat
            DB::table('editlog')->insert($data);
        });
    }
}
