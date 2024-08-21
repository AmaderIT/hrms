<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionSupervisor extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = "1";
    const STATUS_DISABLE = "0";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function officeDivision()
    {
        return $this->belongsTo(OfficeDivision::class);
    }

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo
     */
    public function supervisedBy()
    {
        return $this->belongsTo(User::class, "supervised_by")->select("id", "name", "email", "phone", "fingerprint_no");
    }

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereStatus(self::STATUS_ACTIVE);
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
