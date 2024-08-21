<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryMeta extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "salary_meta";

    /**
     * @var string
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];
}
