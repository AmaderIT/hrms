<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeStatus extends Model
{
    use HasFactory, LogsActivity;

    /**
     * @var string
     */
    protected $table = "employee_status";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ["action_date"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return BelongsTo
     */
    public function actionReason()
    {
        return $this->belongsTo(ActionReason::class);
    }

    /**
     * @return BelongsTo
     */
    public function actionTakenBy()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Employee Status has been {$eventName}";
    }
}
