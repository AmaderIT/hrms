<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCronLog extends Model
{
    use HasFactory;
    /**
     * @var string
     */
    protected $table = "daily_cron_log";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];
}
