<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Promotion extends Model
{
    use HasFactory, LogsActivity;

    const TYPE_INTERNEE = "Internee";
    const TYPE_PROVISION = "Provision";
    const TYPE_PERMANENT = "Permanent";
    const TYPE_PROMOTED = "Promoted";
    const TYPE_CONTRACTUAL = "Contractual";
    const TYPE_TRANSFERRED = "Transferred";
    const TYPE_INCREMENT = "Increment";
    const TYPE_REJOIN = "Rejoin";
    const TYPE_TERMINATED = "Terminated";
    const TYPE_JOIN = "Join";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ["effective_from", "promoted_date"];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select("id", "name", "email", "phone", "fingerprint_no");
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
    public function department()
    {
        return $this->belongsTo(Department::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function allDepartment()
    {
        return $this->belongsTo(Department::class,'department_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class)->select("id", "title");
    }

    /**
     * @return BelongsTo
     */
    public function payGrade()
    {
        return $this->belongsTo(PayGrade::class)->select(
            "id", "name", "range_start_from", "range_end_to", "percentage_of_basic", "based_on",
            "overtime_formula", "holiday_allowance_formula", "weekend_allowance_formula", "tax_id"
        );
    }

    /**
     * @return BelongsTo
     */
    public function workSlot()
    {
        return $this->belongsTo(WorkSlot::class, "workslot_id");
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Promotion Status has been {$eventName}";
    }

    public static function employmentType(){
        return [
          self::TYPE_INTERNEE => 'Internee',
          self::TYPE_PROVISION => 'Provision',
          self::TYPE_CONTRACTUAL => 'Contractual',
          self::TYPE_PERMANENT => 'Permanent',
          self::TYPE_PROMOTED => 'Promoted'
        ];
    }
    public static function employeeActionTypes(){
        return [
            self::TYPE_TRANSFERRED => 'Transferred',
            self::TYPE_INCREMENT => 'Increment'
        ];
    }

    public static function promoteTypes(){
        return [
            self::TYPE_INTERNEE => 'Internee',
            self::TYPE_PROVISION => 'Provision',
            self::TYPE_PROMOTED => 'Promoted',
            self::TYPE_PERMANENT => 'Permanent',
            self::TYPE_CONTRACTUAL => 'Contractual',
            self::TYPE_INCREMENT => 'Increment'
        ];
    }
    public static function joiningTypes(){
        return [
            self::TYPE_JOIN => 'Join',
            self::TYPE_REJOIN => 'Re-join'
        ];
    }
}
