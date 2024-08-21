<?php

namespace App\Models;

use App\Library\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLoan extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;
    const AMOUNT_APPLIED = 4;
    const AMOUNT_APPROVED = 5;
    const AMOUNT_CHANGE_APPLIED = 6;
    const AMOUNT_REJECTED = 3;
    const DEDUCTION_PENDING = 2;
    const DEDUCTED = 1;

    const STATUS = [
        self::AMOUNT_APPLIED => 'Amount Applied',
        self::AMOUNT_APPROVED => 'Amount Approved',
        self::AMOUNT_CHANGE_APPLIED => 'Amount Change Applied',
        self::DEDUCTION_PENDING => 'Deduction Pending',
        self::DEDUCTED => 'Paid',
        self::AMOUNT_REJECTED => 'Rejected',
    ];

    const YES = 'Y';
    const NO = 'N';

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this) . " has been {$eventName}";
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
