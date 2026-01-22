<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZoomModel extends Model
{
    protected $table = 'zoom';
    public $timestamps = false;
    protected $fillable = ['tanggal_zoom', 'rapat', 'zoom_id', 'zoom_password','zoom_link', 'host'];
}
