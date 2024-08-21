<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;

class Requisition extends Model
{
    use HasFactory, LogsActivity;

    const PRIORITY_TODAY = 0;
    const PRIORITY_WITHIN_3_DAYS = 1;
    const PRIORITY_WITHIN_7_DAYS = 2;
    const PRIORITY_WITHIN_10_DAYS = 3;

    const STATUS_NEW = "0";
    const STATUS_IN_PROGRESS = "1";
    const STATUS_DELIVERED = "2";
    const STATUS_REJECTED = "3";
    const STATUS_RECEIVED = "4";

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
    public function department()
    {
        return $this->belongsTo(Department::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function appliedBy()
    {
        return $this->belongsTo(User::class, "applied_by")->select("id", "name", "email", "phone", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, "approved_by")->select("id", "name", "email", "phone", "fingerprint_no");
    }

    /**
     * @return HasMany
     */
    public function details()
    {
        return $this->hasMany(RequisitionDetails::class)->with("measurement");
    }

    /**
     * @param $value
     * @return string
     */
    public function getAppliedDateAttribute($value)
    {
        return Carbon::createFromDate($value)->format('M d, Y');
    }

    /**
     * @var array
     */
    protected $dates = ["applied_date"];

    /**
     * @param Builder $query
     * @param Carbon  $startDate
     * @param Carbon  $endDate
     * @return Builder
     */
    public function scopeWhereDateBetween(Builder $query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween(DB::raw("DATE(applied_date)"), array(
            $startDate->format("Y-m-d"), $endDate->format("Y-m-d")
        ));
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
