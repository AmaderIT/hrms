<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Department extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return BelongsToMany
     */
    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function officeDivision()
    {
        return $this->belongsTo(OfficeDivision::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function supervisor()
    {
        return $this->belongsTo(DepartmentSupervisor::class, "id", "department_id")->where("status", DepartmentSupervisor::STATUS_ACTIVE);
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Department has been {$eventName}";
    }

    /**
     * @return BelongsTo
     */
    public function relaxDaySetting()
    {
        return $this->hasOne(RelaxDaySetting::class);
    }

    /**
     * @return BelongsTo
     */
    public function weeklyHoliday()
    {
        return $this->hasOne(WeeklyHoliday::class)->where('effective_date', '<=', date('Y-m-d'))->where(function($q){
            $q->where('end_date', '>=', date('Y-m-d'))->orWhereNull('end_date');
        })->orderByRaw('ABS( DATEDIFF( effective_date, NOW() ) )');
    }

    /**
     * @return BelongsTo
     */
    public function leaveAllocation()
    {
        return $this->hasOne(LeaveAllocation::class)->where('year', '=', date('Y'));
    }

    /**
     * @return BelongsTo
     */
    public function lateDeduction()
    {
        return $this->hasOne(LateDeduction::class);
    }


}
