<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitKerjaModel extends Model
{
    protected $table = 'unit_kerja';
    public $timestamps = false;
    
    public function users()
    {
        return $this->belongsToMany('App\UserModel', 'unit_kerja_user', 'unit_kerja_model_id', 'user_model_id');
    }
    
}
