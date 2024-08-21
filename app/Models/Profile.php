<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Profile extends Model
{
    use HasFactory, LogsActivity;

    const GENDER_MALE = 'Male';
    const GENDER_FEMALE = 'Female';
    const GENDER_OTHER = "Other";

    const MARITAL_STATUS_SINGLE = 'Single';
    const MARITAL_STATUS_MARRIED = 'Married';

    const RELIGION_ISLAM = 'Islam';
    const RELIGION_HINDU = 'Hinduism';
    const RELIGION_CHRISTIANITY = 'Christianity';
    const RELIGION_BUDDHISM = 'Buddhism';
    const RELIGION_OTHER = 'Other';

    const EMPLOYMENT_STATUS_CONTRACTUAL = 'Contractual';
    const EMPLOYMENT_STATUS_PROVISION = 'Provision';
    const EMPLOYMENT_STATUS_PERMANENT = 'Permanent';

    const BLOOD_GROUP_A_POSITIVE = 'A+';
    const BLOOD_GROUP_A_NEGATIVE = 'A-';
    const BLOOD_GROUP_AB_POSITIVE = 'AB+';
    const BLOOD_GROUP_AB_NEGATIVE = 'AB-';
    const BLOOD_GROUP_B_POSITIVE = 'B+';
    const BLOOD_GROUP_B_NEGATIVE = 'B-';
    const BLOOD_GROUP_O_POSITIVE = 'O+';
    const BLOOD_GROUP_O_NEGATIVE = 'O-';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ["dob"];

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Profile Status has been {$eventName}";
    }
}
