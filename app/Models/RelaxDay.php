<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class RelaxDay extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    /**
     * @var string
     */
    protected $table = "relax_day";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class)->with('relaxDaySetting')->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function createdUser()
    {
        return $this->belongsTo(User::class,'created_by')->select("id", "name", "fingerprint_no");
    }
}
