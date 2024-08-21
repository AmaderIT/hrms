<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveAllocation extends Model
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
     * @return HasMany
     */
    public function leaveAllocationDetails()
    {
        return $this->hasMany(LeaveAllocationDetails::class);
    }

    /**
     * @return BelongsTo
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
