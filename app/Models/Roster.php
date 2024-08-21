<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Library\UsesUuid;

class Roster extends Model
{
    use HasFactory, UsesUuid;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_CANCEL = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;

    const EMPLOYEE_TYPE = 1;
    const DEPARTMENT_TYPE = 2;

    const STS = ["Pending","Approved","Cancel"];

    public $table = 'rosters';

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $dates = ["start_date", "end_date", "active_date", "created_at", "updated_at"];

    public static function getTableName()
    {
        return (new self())->getTable();
    }

    // /**
    //  * @param $value
    //  * @return false|string
    //  */
    // public function setWeeklyHolidaysAttribute($value)
    // {
    //     return $this->attributes["weekly_holidays"] = json_encode($value);
    // }

    // /**
    //  * @param $value
    //  * @return mixed
    //  */
    // public function getWeeklyHolidaysAttribute($value)
    // {
    //     return json_decode($value);
    // }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name', 'fingerprint_no', 'email');
    }

    /**
     * @return BelongsTo
     */
    public function workSlot()
    {
        return $this->belongsTo(WorkSlot::class);
    }

    /**
     * @return BelongsTo
     */
    public function officeDivision()
    {
        return $this->belongsTo(OfficeDivision::class)->select('id', 'name');
    }

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class)->select('id', 'name', 'office_division_id');
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
