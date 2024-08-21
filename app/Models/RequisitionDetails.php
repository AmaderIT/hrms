<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class RequisitionDetails extends Model
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
     * @return BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(RequisitionItem::class, "requisition_item_id");
    }

    public function measurement(){
        return $this->belongsTo(MeasurementDetails::class, "measure_id","id");
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this) . " has been {$eventName}";
    }
}
