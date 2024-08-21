<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentSupervisor extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = "1";
    const STATUS_DISABLE = "0";

    /**
     * @var string
     */
    protected $table = "department_supervisor";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function officeDivision()
    {
        return $this->belongsTo(OfficeDivision::class);
    }

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo
     */
    public function supervisedBy()
    {
        return $this->belongsTo(User::class, "supervised_by")->select("id", "name", "email", "phone", "fingerprint_no");
    }

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereStatus(self::STATUS_ACTIVE);
    }
}
