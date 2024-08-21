<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Bonus extends Model
{
    use HasFactory, LogsActivity;

    const TYPE_BASIC = "Basic";
    const TYPE_GROSS = "Gross";
    const STATUS_UNPAID = "0";
    const STATUS_PAID = "1";
    const SIX_MONTH = 6;
    const THREE_MONTH = 3;

    const EMPLOYMENT_PERIODS = [
        self::SIX_MONTH => '6 Month +',
        self::THREE_MONTH => '3 Month +',
    ];

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this) . " has been {$eventName}";
    }
}
