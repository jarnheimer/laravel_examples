<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserActivities extends Model
{
    protected $table = 'la_user_activities';
    
    public function activity()
    {
        return $this->belongsTo('App\Model\Activity');
    }
    
    public function userActivitiesAttributes()
    {
        return $this->hasMany('App\Model\UserActivitiesAttributes', 'user_activity_id');
    }
}
