<?php

namespace App\Models;

use App\Library\UsesUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveRequest extends Model
{
    use HasFactory, LogsActivity, UsesUuid;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2; // TODO: Replace it by the const STATUS_CANCEL
    const STATUS_CANCEL = 2;
    const STATUS_AUTHORIZED = 3;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @var string[]
     */
    protected $dates = ["from_date", "to_date"];

    /**
     * @return BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(User::class, "user_id")->select("id", "name", "email", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function authorizedBy()
    {
        return $this->belongsTo(User::class, "authorized_by")->select("id", "name", "email", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, "approved_by")->select("id", "name", "email", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function appliedTo()
    {
        return $this->belongsTo(User::class, "approved_by")->select("id", "name", "email");
    }

    /**
     * @param Builder $query
     * @param Carbon  $startDate
     * @param Carbon  $endDate
     * @return Builder
     */
    public function scopeWhereCreatedBetween(Builder $query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween(DB::raw("DATE(created_at)"), array(
            $startDate->format("Y-m-d"), $endDate->format("Y-m-d")
        ));
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Leave request has been {$eventName}";
    }
}
