<?php

namespace App\Models;

use App\Library\UsesUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Loan extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    const STATUS_ACTIVE = "Active";
    const STATUS_PAID = "Paid";
    const STATUS_PENDING = "Pending";
    const STATUS_REJECT = "Reject";
    const STATUS_HOLD = "Hold";
    const TYPE_LOAN = "Loan";
    const TYPE_ADVANCE = "Advance";

    const DEPARTMENTAL_APPROVAL = 'departmental';
    const DIVISIONAL_APPROVAL = 'divisional';
    const HR_APPROVAL = 'hr';
    const ACCOUNTS_APPROVAL = 'accounts';
    const MANAGERIAL_APPROVAL = 'managerial';

    const LOAN_TYPES = [
        self::TYPE_LOAN => 'Loan',
        self::TYPE_ADVANCE => 'Advance',
    ];

    const LOAN_STATUS = [
        self::STATUS_PENDING => self::STATUS_PENDING,
        self::STATUS_ACTIVE => self::STATUS_ACTIVE,
        self::STATUS_PAID => self::STATUS_PAID,
        self::STATUS_REJECT => self::STATUS_REJECT,
        self::STATUS_HOLD => self::STATUS_HOLD,
    ];

    const APPROVAL_TYPE = [self::DEPARTMENTAL_APPROVAL, self::DIVISIONAL_APPROVAL, self::HR_APPROVAL, self::ACCOUNTS_APPROVAL, self::MANAGERIAL_APPROVAL];

    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /** Policy Related Constants **/
    const SIX_MONTH_DURATION = 1;
    const LOAN_AMOUNT = 2;
    const MAX_12_MONTH_DEDUCTION = 3;
    const FIRST_LOAN_UNPAID = 4;
    const SIX_MONTH_GAP = 5;
    const SEVEN_BUSINESS_DAY = 6;
    const LOAN_RESIGNATION = 7;
    const THREE_MONTH_DURATION = 8;
    const ADVANCE_AMOUNT = 9;
    const ADVANCE_SIX_MONTH_GAP = 10;
    const FIFTH_20TH_DAY = 11;
    const RUNNING_MONTH_DEDUCTION = 12;

    const LOAN_POLICIES = [
        self::SIX_MONTH_DURATION => 'An employee will be eligible for the loan only after he/she has worked for the company for at least six
months.',
        self::LOAN_AMOUNT => 'An eligible employee may borrow up to 100% of their monthly gross salary. In specific cases, the loan
amount could be two times the amount of the applicant’s monthly gross salary.',
        self::MAX_12_MONTH_DEDUCTION => 'The loan will be deducted from the monthly salaries of the employee within 12 (twelve) months from
disbursement.',
        self::FIRST_LOAN_UNPAID => 'An employee cannot apply for a second loan before the first loan is repaid in full.',
        self::SIX_MONTH_GAP => 'There shall be a minimum of 06 (six) months gap before applying for another loan from the date the
previous loan is repaid in full.',
        self::SEVEN_BUSINESS_DAY => 'After the application of the loan, it will take at least seven business days to do the paperwork.',
        self::LOAN_RESIGNATION => 'In case of resignation or termination of employment for the employee who has received a loan but has
not fully repaid, the remaining loan amount will be adjusted from that employee’s net payable amount.',
    ];

    const ADVANCE_POLICIES = [
        self::THREE_MONTH_DURATION => 'An employee will be eligible for salary advance only after he/she has worked for the company for at
least three months.',
        self::ADVANCE_AMOUNT => 'The salary advance amount may be a maximum of 50% of the respective employee’s gross salary',
        self::ADVANCE_SIX_MONTH_GAP => 'An employee cannot apply for an advance in six months after availing an advance.',
        self::FIFTH_20TH_DAY => 'Salary advances cannot be allowed from the 5th to the 20th day of any month.',
        self::RUNNING_MONTH_DEDUCTION => 'The withdrawn salary advance will be deducted from the salary of the running month.',
    ];

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select("id", "name", "email", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function authorizedBy()
    {
        return $this->belongsTo(User::class, "authorized_by")->select("id", "name", "email", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, "approved_by")->select("id", "name", "email", "fingerprint_no");
    }

    /**
     * @return HasMany
     */
    public function userLoans()
    {
        return $this->hasMany(UserLoan::class)->orderBy('id', 'asc');
    }

    /**
     * @return HasMany
     */
    public function userLoansByDesc()
    {
        return $this->hasMany(UserLoan::class)->orderBy('year', 'DESC')->orderBy('month', 'DESC');
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this) . " has been {$eventName}";
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

    /**
     * @return BelongsTo
     */
    public function paidBy()
    {
        return $this->belongsTo(User::class, "loan_paid_by")->select("id", "name", "fingerprint_no");
    }

    public function oldDataMigration()
    {
        $loans = self::get();

        foreach ($loans as $loan) {

            $instalmentStartDate = date('Y-m-01', strtotime("+1 months", strtotime($loan->created_at)));

            # Set approvals
            if ($loan->hr_approval_by) { # HR approved loan considered as approved loan

                $approvedDate = (!empty($loan->hr_approved_date)) ? $loan->hr_approved_date : Carbon::now();
                $adminId = User::getAdminId();

                $loan->departmental_approval_status = Loan::STATUS_APPROVED;
                $loan->divisional_approval_status = Loan::STATUS_APPROVED;
                $loan->hr_approval_status = Loan::STATUS_APPROVED;
                $loan->accounts_approval_status = Loan::STATUS_APPROVED;
                $loan->managerial_approval_status = Loan::STATUS_APPROVED;

                $loan->divisional_approved_date = $approvedDate;
                $loan->hr_approved_date = $approvedDate;
                $loan->accounts_approved_date = $approvedDate;
                $loan->managerial_approved_date = $approvedDate;

                $loan->divisional_approval_by = $adminId;
                $loan->accounts_approval_by = $adminId;
                $loan->managerial_approval_by = $adminId;

                $loan->loan_paid_status = Loan::STATUS_APPROVED;
                $loan->loan_paid_by = $loan->hr_approval_by;
                $loan->loan_paid_date = $approvedDate;

                $loan->accept_policy = 'Y';
                $instalmentStartDate = date('Y-m-01', strtotime("+1 months", strtotime($approvedDate)));
            }

            if ($loan->departmental_approval_by) {
                $loan->departmental_approval_status = Loan::STATUS_APPROVED;
                $loan->departmental_approved_date = (!empty($loan->departmental_approved_date)) ? $loan->departmental_approved_date : Carbon::now();
            }

            # Define instalment start month according loan-advance created date orAnd loan-advance approved date
            $loan->installment_start_month = date('m-Y', strtotime($instalmentStartDate));

            # Operations for Non-Paid Loans
            $remainingLoanInstallments = [];
            $totalInstalmentAmount = 0;
            $totalInsertedInstalment = 0;

            if ($loan->status == Loan::STATUS_ACTIVE) {
                if (count($loan->userLoans) > 0) {
                    $totalInstalmentAmount = $loan->userLoans->sum('amount_paid');
                    $totalInsertedInstalment = $loan->userLoans->count();
                    $lastInstalment = $loan->userLoansByDesc->first();
                    $lastInstalmentMonthYear = !empty($lastInstalment->month) ? date('Y-m-d', strtotime(date('d-m-Y', strtotime("01-$lastInstalment->month-$lastInstalment->year")))) : null;
                    $instalmentStartDate = date('Y-m-01', strtotime("+1 months", strtotime($lastInstalmentMonthYear)));

                    # Define instalment start month according to first instalment from user_loan table
                    $firstInstalment = $loan->userLoans->first();
                    $firstInstalmentMonth = !empty($firstInstalment->month) ? date('m-Y', strtotime(date('d-m-Y', strtotime("01-$firstInstalment->month-$firstInstalment->year")))) : null;
                    $loan->installment_start_month = $firstInstalmentMonth;
                }

                $unpaidAmountTillNow = (double)($loan->loan_amount - $totalInstalmentAmount);
                $totalRemainingInstallment = ($loan->loan_tenure - $totalInsertedInstalment);

                if ($totalRemainingInstallment <= 0 && $unpaidAmountTillNow <= 0) {
                    continue;
                } else if ($totalRemainingInstallment == 0 && $unpaidAmountTillNow > 0) {
                    $instalmentAmount = $unpaidAmountTillNow;
                    $totalRemainingInstallment = 1;
                } else {
                    $instalmentAmount = round($unpaidAmountTillNow / $totalRemainingInstallment, 2);
                }

                # Organize instalment data
                for ($i = 0; $i < $totalRemainingInstallment; $i++) {
                    $instalmentMonthYear = date('m-Y', strtotime("+$i months", strtotime($instalmentStartDate)));

                    $datePicker = \Functions::getMonthAndYearFromDatePicker($instalmentMonthYear);
                    $month = $datePicker["month"];
                    $year = $datePicker["year"];

                    $remainingLoanInstallments[] = [
                        'uuid' => \Functions::getNewUuid(),
                        'user_id' => $loan->user_id,
                        'loan_id' => $loan->id,
                        'amount_paid' => $instalmentAmount,
                        'month' => $month,
                        'year' => $year,
                        'remark' => 'migrated from old loan-advance',
                        'status' => ($loan->hr_approval_by == 1) ? UserLoan::AMOUNT_APPROVED : UserLoan::AMOUNT_APPLIED,
                        'created_by' => Auth::id(),
                        'created_at' => Carbon::now(),
                    ];
                }
            }

            # Operations for Paid Loans
            if ($loan->status == Loan::STATUS_PAID) {
                if (!empty($loan->userLoans)) {
                    # Define instalment start month according to first instalment from user_loan table
                    $firstInstalment = $loan->userLoans->first();
                    $firstInstalmentMonth = !empty($firstInstalment->month) ? date('m-Y', strtotime(date('d-m-Y', strtotime("01-$firstInstalment->month-$firstInstalment->year")))) : null;
                    $loan->installment_start_month = $firstInstalmentMonth;
                }

                if (empty($loan->instalment_paid_at)) {
                    $loan->instalment_paid_at = $loan->updated_at;
                }
            }

            # Set Loan Status
            if ($loan->status == Loan::STATUS_ACTIVE && $loan->hr_approval_by != 1) {
                $loan->status = Loan::STATUS_PENDING;
            }

            try {
                DB::beginTransaction();

                $loan->update();

                if (count($remainingLoanInstallments) > 0) {
                    UserLoan::insert($remainingLoanInstallments);
                }

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();

                Log::debug('Loan/Advance old data migration error: ' . $exception->getMessage());
            }

        }
    }
}
