<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class DepartmentRoaster extends Model
{
    use HasFactory, LogsActivity;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_CANCEL = 2;

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var bool
     */
    public $timestamps = true;

    protected $dates = ["active_from", "created_at", "updated_at"];
}
