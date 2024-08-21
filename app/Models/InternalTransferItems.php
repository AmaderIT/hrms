<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class InternalTransferItems extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(RequisitionItem::class)->select('id','name','code');
    }

    public function otherItem()
    {
        return $this->belongsTo(OtherRequisitionItem::class)->select('id','name','code');
    }

    public function uomName()
    {
        return $this->belongsTo(Unit::class,'uom')->select('id','name');
    }

    public function measurement(){
        return $this->belongsTo(MeasurementDetails::class, "measure_id","id");
    }

    public function itemMeasurements(){
        return $this->hasMany(MeasurementDetails::class, "requisition_item_id",'item_id')->where('deleted_at', NULL);
    }

}
