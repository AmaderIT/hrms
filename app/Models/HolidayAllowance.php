<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayAllowance extends Model
{
    use HasFactory;

    const TYPE_WEEKLY = "weekly";
    const TYPE_ORGANIZATIONAL = "organizational";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];
}
