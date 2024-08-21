<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class RequisitionItem extends Model
{
    use HasFactory, LogsActivity;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this) . " has been {$eventName}";
    }


    public function itemMeasurements(){
        return $this->hasMany(MeasurementDetails::class, "requisition_item_id",'id')->where('deleted_at', NULL);
    }

    public function unitName(){
        return $this->belongsTo(Unit::class, "unit_id",'id')->select('id','name');
    }
}
