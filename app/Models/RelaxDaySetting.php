<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelaxDaySetting extends Model
{
    use HasFactory, SoftDeletes;

    CONST EMPLOYEE_TYPE = 1;

    CONST DEPARTMENT_TYPE = 2;

    CONST FUTURE_WEEK_LIMIT = 6;

    protected $table = 'relax_day_settings';

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class)->select("id", "name");
    }


}
