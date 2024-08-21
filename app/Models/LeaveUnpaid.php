<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LeaveUnpaid extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_APPLIED = 2;

    /**
     * @var string
     */
    protected $table = "leave_unpaid";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @param Builder $query
     * @param Carbon  $startDate
     * @param Carbon  $endDate
     * @return Builder
     */
    public function scopeWhereDateBetween(Builder $query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween(DB::raw("DATE(leave_date)"), array(
            $startDate->format("Y-m-d"), $endDate->format("Y-m-d")
        ));
    }
}
