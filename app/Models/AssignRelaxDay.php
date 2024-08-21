<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class AssignRelaxDay extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    const APPROVAL_PENDING = 0;
    const APPROVAL_CONFIRMED = 1;
    protected $table = 'assign_relax_day';
    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public function relaxDate()
    {
        return $this->belongsTo(RelaxDay::class,'relax_day_id')->select('id','date');
    }
}
