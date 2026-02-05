<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RapatCustomField extends Model
{
    protected $table = 'rapat_custom_fields';
    
    protected $fillable = ['rapat_id', 'field_key', 'field_value', 'field_order'];
    
    /**
     * Relationship to parent meeting (rapat)
     */
    public function rapat()
    {
        return $this->belongsTo('App\RapatModel', 'rapat_id', 'id');
    }
}
