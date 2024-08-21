<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersDailyWorkSlot extends Model
{
    use HasFactory;
    protected $table = 'users_daily_work_slot';
    protected $guarded = ['id'];
    protected $hidden = ["created_at", "updated_at"];
}
