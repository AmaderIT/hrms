<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;

class Tax extends Model
{
    use HasFactory, LogsActivity;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const MEDICAL_TAX_EXEMPTED_PERCENTAGE_OF_BASIC = 10;

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return HasMany
     */
    public function rules()
    {
        return $this->hasMany(TaxRule::class);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where("status", self::STATUS_ACTIVE)->orderBy("id", "desc");
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
