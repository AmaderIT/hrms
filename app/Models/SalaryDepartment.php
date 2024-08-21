<?php

namespace App\Models;

use App\Library\UsesUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryDepartment extends Model
{
    use HasFactory, UsesUuid;

    const STATUS_UNPAID = "0";
    const STATUS_PAID = "1";

    const STATUS_PENDING = "0";
    const STATUS_APPROVED = "1";
    const STATUS_REJECTED = "2";

    /**
     * @var string
     */
    protected $table = "salary_department";

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

    public function migrateOldSalaryData()
    {
        $salaryDepartments = self::select('id', 'office_division_id', 'department_id', 'month', 'year')->get();

        foreach ($salaryDepartments as $salaryDepartment) {

            try {
                DB::beginTransaction();

                Salary::where([
                    'office_division_id' => $salaryDepartment->office_division_id,
                    'department_id' => $salaryDepartment->department_id,
                    'year' => $salaryDepartment->year,
                    'month' => $salaryDepartment->month
                ])->update(['salary_department_id' => $salaryDepartment->id]);

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();

                Log::debug('Salary Old Data Migration: ' . $exception->getMessage());
            }

        }
    }
}
