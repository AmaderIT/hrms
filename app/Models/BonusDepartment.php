<?php

namespace App\Models;

use App\Library\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusDepartment extends Model
{
    use HasFactory, UsesUuid;

    const STATUS_UNPAID = "0";
    const STATUS_PAID = "1";

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * @var string
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
     * @return BelongsTo
     */
    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
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
