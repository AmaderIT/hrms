<?php

namespace App\Models;

use App\Library\UsesUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;

class OnlineAttendance extends Model
{
    use HasFactory, LogsActivity, UsesUuid;

    const APPROVED = 1;
    const PENDING = 2;
    const AUTHORIZED = 3;
    const REJECTED = 99;

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
    public function employee()
    {
        return $this->belongsTo(User::class, "user_id")->select("id", "name", "email", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function appliedBy()
    {
        return $this->belongsTo(User::class, "applied_by")->select("id", "name", "email");
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, "user_id")->select("id", "name", "email", 'fingerprint_no');
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
        return class_basename($this) . " has been {$eventName}";
    }

    /**
     * @return HasOne
     */
    public function todayTimeIn()
    {
        $onlineAttendanceConfig = \Functions::getOnlineAttendanceInfo();
        return $this->hasOne(OnlineAttendance::class, "user_id", "user_id")
            ->where("time_in", ">=", $onlineAttendanceConfig['checkin_start_time'])
            ->where("time_in", "<=", $onlineAttendanceConfig['checkin_end_time'])
            ->where("status", "!=", OnlineAttendance::REJECTED)
            ->orderBy("time_in")
            ->select("id", "user_id", "time_in");
    }

    /**
     * @return HasOne
     */
    public function todayTimeOut()
    {
        $onlineAttendanceConfig = \Functions::getOnlineAttendanceInfo();
        return $this->hasOne(OnlineAttendance::class, "user_id", "user_id")
            ->where("time_out", ">=", $onlineAttendanceConfig['checkin_start_time'])
            ->where("time_out", "<=", Carbon::parse("tomorrow {$onlineAttendanceConfig['late_checkout_end_time']}"))
            ->where("status", "!=", OnlineAttendance::REJECTED)
            ->orderByDesc("time_out")
            ->select("id", "user_id", "time_out");
    }

    /**
     * @return HasMany
     */
    public function timeInThisMonth()
    {
        return $this->hasMany(OnlineAttendance::class, "user_id", "user_id")
            ->whereMonth("time_in", date("m"))
            ->whereYear("time_in", date("Y"))
            ->where("status", "!=", OnlineAttendance::REJECTED)
            ->orderByDesc("id")
            ->select("id", "user_id", "time_in")
            ->groupBy(DB::raw('DATE(time_in)'));
    }

    /**
     * @return HasMany
     */
    public function timeOutThisMonth()
    {
        $userId = auth()->id();

        return $this->hasMany(OnlineAttendance::class, "user_id", "user_id")
            ->whereMonth("time_out", date("m"))
            ->whereYear("time_out", date("Y"))
            ->where("status", "!=", OnlineAttendance::REJECTED)
            ->orderByDesc("id")
            ->where("user_id", $userId)
            ->select("id", "user_id", "time_out");
    }
}
