<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const SALARY_GENERATED = 'Salary Generated';
    const SALARY_RE_GENERATED = 'Salary Re-Generated';
    const DEPARTMENT_APPROVED = 'Department Approved';
    const DEPARTMENT_REJECTED = 'Department Rejected';
    const DIVISION_APPROVED = 'Division Approved';
    const DIVISION_REJECTED = 'Division Rejected';
    const HR_APPROVED = 'HR Approved';
    const HR_REJECTED = 'HR Rejected';
    const ACCOUNTS_APPROVED = 'Accounts Approved';
    const ACCOUNTS_REJECTED = 'Accounts Rejected';
    const MANAGEMENT_APPROVED = 'Management Approved';
    const MANAGEMENT_REJECTED = 'Management Rejected';
    const SALARY_PAID = 'Salary Paid';
    const TAX_ADJUSTMENT = 'Tax amount adjusted';

    public static function generateSalaryLog($deptUuid, $action, $actionTakenBy, $remark = null){
        self::create([
            'salary_department_uuid' => $deptUuid,
            'action' => $action,
            'remark' => $remark,
            'action_taken_by' => $actionTakenBy
        ]);
    }
}
