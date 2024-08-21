<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class DepartmentLeaveEncashment extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    const APPROVAL_PENDING = 0;
    const APPROVAL_CONFIRMED = 1;
    const APPROVAL_REJECTED = 2;
    protected $table = 'department_leave_encashment';
    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public function employeeLeaveEncashment(){
        return $this->hasMany(EmployeeLeaveEncashment::class, 'department_leave_encashment_id', 'id');
    }

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
    public function preparedBy()
    {
        return $this->belongsTo(User::class, "prepared_by")->select("id", "name", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function divisionalApprovalBy()
    {
        return $this->belongsTo(User::class, "divisional_approval_by")->select("id", "name", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function departmentalApprovalBy()
    {
        return $this->belongsTo(User::class, "departmental_approval_by")->select("id", "name", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function hrApprovalBy()
    {
        return $this->belongsTo(User::class, "hr_approval_by")->select("id", "name", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function accountsApprovalBy()
    {
        return $this->belongsTo(User::class, "accounts_approval_by")->select("id", "name", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function managerialApprovalBy()
    {
        return $this->belongsTo(User::class, "managerial_approval_by")->select("id", "name", "fingerprint_no");
    }

}
