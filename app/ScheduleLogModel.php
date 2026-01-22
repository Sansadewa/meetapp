<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleLogModel extends Model
{
    protected $table = 'schedule_log';
    protected $primaryKey = 'id';
    const CREATED_AT = 'trigger_time';
    const UPDATED_AT = 'trigger_time';
}
