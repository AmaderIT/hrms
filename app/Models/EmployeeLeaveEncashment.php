<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeaveEncashment extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'employee_leave_encashment';
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
    public function employeeInformation()
    {
        return $this->belongsTo(User::class, "user_id")->select("id", "name", "fingerprint_no");
    }
}
