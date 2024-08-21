<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;

class ActionReason extends Model
{
    use HasFactory, LogsActivity;

    const TYPE_JOIN = "Join";
    const TYPE_REJOIN = "Re Join";
    const TYPE_RESIGN = "Resign";
    const TYPE_SUSPEND = "Suspend";
    const TYPE_TERMINATE = "Terminate";

    protected $guarded = ["id"];

    /**
     * @return HasMany
     */
    public function child()
    {
        return $this->hasMany(ActionReason::class, "parent_id", "id")->select("id", "parent_id", "name", "reason");
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ActionReason::class, "parent_id")->select("id", "parent_id", "name", "reason");
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeTypes(Builder $query)
    {
        return $query->where("parent_id", "==", 0)->select("id", "parent_id", "name", "reason");
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeReasons(Builder $query)
    {
        return $query->where("parent_id", "!=", 0)->select("id", "parent_id", "name", "reason");
    }

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeReasonForTermination(Builder $query)
    {
        $terminationId = ActionReason::where("name", ActionReason::TYPE_TERMINATE)->pluck("id")->first();
        return ActionReason::where("parent_id", $terminationId)->select("id", "parent_id", "name", "reason");
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Action Reason/Type has been {$eventName}";
    }
}
