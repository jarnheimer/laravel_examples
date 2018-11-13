<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class Activity extends Model
{
    protected $table = 'la_activities';
    protected $appends = ['attributes_full_list', 'activity_type_name'];
    
    public function scopeFindByKeyword($q, $keyword) {
        $q->where('name', 'like', DB::raw("'%".$keyword."%'"));
    }
    
    public function scopeConfirmed($q) {
        $q->where('confirmed', 1);
    }
    
    public function getNameDisplayedAttribute($value)
    {
        return $this->name;
    }    
    
    public function type()
    {
        return $this->belongsTo('App\Model\ActivityType', 'activity_type', 'id');
    }    
    
    public function getActivityTypeNameAttribute($value)
    {
        return object_get($this, 'type.name');
    }
    
    public function activity_attributes()
    {
        return $this->hasMany('App\Model\ActivityAttribute', 'activity_id', 'id');
    }
    
    public function getAttributesListAttribute()
    {
        $attributes_list_full = [];
        $activity_attributes = $this->activity_attributes;
        foreach ($activity_attributes as $activity_attribute) {
            $attributes_list_full[$activity_attribute->attribute_id] = [
                'name' => $activity_attribute->attribute->name,
                'value' => $activity_attribute->attribute_value
            ];
        }
        
        $attributes = Attributes::where('activity_type', $this->activity_type)->get();
        foreach ($attributes as $attribute) {
            if (!array_get($attributes_list_full, $attribute->id)) {
                $attributes_list_full[$attribute->id] = [
                    'name' => $attribute->name
                ];
            }
        }
        
        return $attributes_list_full;
    }
    
    public function getAttributesFullListAttribute($value)
    {
        $attributes_id = $this->activity_attributes()->pluck('attribute_id');
        return Attributes::whereIn('id', $attributes_id)->get()->toArray();
    }
}
