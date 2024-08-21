<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;

class Termination extends Model
{
    use HasFactory, LogsActivity;

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @var string
     */
    protected $table = "employee_status";

    /**
     * @var string[]
     */
    protected $dates = ["action_date"];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        $terminationId = ActionReason::where("name", ActionReason::TYPE_TERMINATE)->pluck("id")->first();
        $ids = ActionReason::where("parent_id", $terminationId)->select("id", "parent_id")->pluck("id")->toArray();

        static::addGlobalScope("action_reason_id", function (Builder $query) use ($ids) {
            $query->whereIn("action_reason_id", $ids);
        });
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select("id", "name", "fingerprint_no", "status");
    }

    /**
     * @return BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(ActionReason::class, "action_reason_id")
            ->with("parent")
            ->where("parent_id", "!=", 0)
            ->select("id", "parent_id", "name", "reason");
    }

    /**
     * @return BelongsTo
     */
    public function actionTakenBy()
    {
        return $this->belongsTo(User::class, "action_taken_by")->select("id", "name", "fingerprint_no");
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Employment Close Status has been {$eventName}";
    }

    public function addNewReason($reason)
    {
        $terminationId = ActionReason::where("name", ActionReason::TYPE_TERMINATE)->pluck("id")->first();
        $payLoads = [
            'parent_id' => $terminationId ?? 0,
            'reason' => $reason
        ];
        return ActionReason::firstOrCreate(['reason' => $reason],$payLoads);
    }

}
