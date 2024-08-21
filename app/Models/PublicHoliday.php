<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class PublicHoliday extends Model
{
    use HasFactory, LogsActivity;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @var string[]
     */
    protected $dates = ["from_date", "to_date"];

    /**
     * @return BelongsTo
     */
    public function holiday()
    {
        return $this->belongsTo(Holiday::class)->select("id", "name");
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Public Holiday has been {$eventName}";
    }
}
