<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'users';
    public $timestamps = false;
    
    public function unitKerja()
    {
        // Arguments: Target Model, Pivot Table Name, Current ID, Target ID
        return $this->belongsToMany('App\UnitKerjaModel', 'unit_kerja_user', 'user_model_id', 'unit_kerja_model_id')
                    ->withPivot('tahun');
    }
    
}

