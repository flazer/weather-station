<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorType extends Model
{
    protected $hidden = array('created_at', 'updated_at');

    public function setUpdatedAtAttribute($value) { }
}
