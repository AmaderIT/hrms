<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Earning extends Model
{
    use HasFactory, LogsActivity;

    const MEDICAL_ALLOWANCE = 'Medical Allowance';

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this) . " has been {$eventName}";
    }
}
