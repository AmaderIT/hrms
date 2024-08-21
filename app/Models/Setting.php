<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];
}
