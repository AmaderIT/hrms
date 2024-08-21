<?php

namespace App\Models;

use App\Library\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Salary extends Model
{
    use HasFactory, UsesUuid;

    const STATUS_PAID = 1;
    const STATUS_UNPAID = 0;

    const MONTH_JANUARY = 1;
    const MONTH_FEBRUARY = 2;
    const MONTH_MARCH = 3;
    const MONTH_APRIL = 4;
    const MONTH_MAY = 5;
    const MONTH_JUNE = 6;
    const MONTH_JULY = 7;
    const MONTH_AUGUST = 8;
    const MONTH_SEPTEMBER = 9;
    const MONTH_OCTOBER = 10;
    const MONTH_NOVEMBER = 11;
    const MONTH_DECEMBER = 12;

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select("id", "name", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function officeDivision(): BelongsTo
    {
        return $this->belongsTo(OfficeDivision::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class)->select("id", "title");
    }

    /**
     * @param $value
     * @return array
     */
    public function getEarningsAttribute($value): array
    {
        return json_decode($value);
    }

    /**
     * @param $value
     * @return void
     */
    public function setEarningsAttribute($value): void
    {
        $this->attributes["earnings"] = json_encode($value);
    }

    /**
     * @param $value
     * @return array
     */
    public function getCashEarningsAttribute($value): array
    {
        return json_decode($value) ?? [];
    }

    /**
     * @param $value
     * @return void
     */
    public function setCashEarningsAttribute($value): void
    {
        $this->attributes["cash_earnings"] = json_encode($value);
    }

    /**
     * @param $value
     * @return array
     */
    public function getDeductionsAttribute($value): array
    {
        return json_decode($value);
    }

    /**
     * @param $value
     * @return void
     */
    public function setDeductionsAttribute($value): void
    {
        $this->attributes["deductions"] = json_encode($value);
    }

    /**
     * @return BelongsTo
     */
    public function currentBank()
    {
        return $this->belongsTo(BankUser::class, "user_id");
    }

    /**
     * @param $userId
     * @return array
     **/
    public static function getLeaveDeductionOnSalary($userId = null, $employeeJoiningDate = null,$paid_status = null)
    {
        $current_year = date("Y");

        $paidCondition ="";
        if($paid_status) {
            $paidCondition = " AND status=".$paid_status;
        }
        $query = "SELECT id, user_id, late_leave_deduction,month,year FROM `salaries` WHERE JSON_EXTRACT(`late_leave_deduction`, '$[*].leave_type_id') IS NOT NULL AND year = $current_year $paidCondition";

        if ($userId) {
            $monthCondition = " ";
            if ($employeeJoiningDate) {
                $monthCondition = " AND month>= " . date('m', strtotime($employeeJoiningDate));
            }
            $query = "SELECT id, user_id, late_leave_deduction,month,year FROM `salaries` WHERE JSON_EXTRACT(`late_leave_deduction`, '$[*].leave_type_id') IS NOT NULL AND year = $current_year AND user_id = $userId $monthCondition $paidCondition";
        }

        $salaries = DB::select($query);

        $lateLeaveDeductions = [];

        foreach ($salaries as $salary) {

            $userId = $salary->user_id;
            $lateDeductions = json_decode($salary->late_leave_deduction, true);

            foreach ($lateDeductions as $lateDeduction) {

                $leaveTypeId = $lateDeduction['leave_type_id'];

                if (empty($lateLeaveDeductions[$userId][$leaveTypeId])) {
                    $lateLeaveDeductions[$userId][$leaveTypeId] = 0;
                }
                $lateLeaveDeductions[$userId][$leaveTypeId] += $lateDeduction['to_be_deducted'];
            }
        }

        return $lateLeaveDeductions;
    }
}
