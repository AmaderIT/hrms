<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class WeeklyHoliday extends Model
{
    use HasFactory, LogsActivity;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class)->select("id", "name");
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Weekly Holiday has been {$eventName}";
    }
}
